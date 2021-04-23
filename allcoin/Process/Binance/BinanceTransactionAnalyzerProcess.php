<?php


namespace AllCoin\Process\Binance;


use AllCoin\Builder\EventTransactionBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Model\EventEnum;
use AllCoin\Model\Transaction;
use AllCoin\Notification\Handler\TransactionAnalyzerNotificationHandler;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairPriceRepositoryInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use Psr\Log\LoggerInterface;

class BinanceTransactionAnalyzerProcess implements ProcessInterface
{
    const STOP_LOSS_PERCENT = 10;
    const BREAK_EVENT_PERCENT = 10;

    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private AssetPairRepositoryInterface $assetPairRepository,
        private AssetPairPriceRepositoryInterface $assetPairPriceRepository,
        private LoggerInterface $logger,
        private DateTimeService $dateTimeService,
        private TransactionAnalyzerNotificationHandler $transactionAnalyzerNotificationHandler,
        private EventTransactionBuilder $eventTransactionBuilder
    )
    {
    }

    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws ItemReadException
     * @throws NotificationHandlerException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assetPairs = $this->assetPairRepository->findAll();

        foreach ($assetPairs as $assetPair) {

            $lastTransaction = $assetPair->getLastTransaction();

            if ($lastTransaction->getDirection() === Transaction::SELL) {
                $this->logger->debug('Not a buy transaction.');
                continue;
            }

            $prices = $this->assetPairPriceRepository->findAllByDateRange(
                $assetPair->getId(),
                $lastTransaction->getCreatedAt(),
                $this->dateTimeService->now()
            );

            $lastPrice = $prices[count($prices) - 1] ?? null;
            if (!$lastPrice) {
                $this->logger->debug('No prices found.');
                continue;
            }

            $unitPrice = $lastTransaction->getAmount() / $lastTransaction->getQuantity();

            $stopLoss = $unitPrice - ($unitPrice * (self::STOP_LOSS_PERCENT / 100));

            $lastBidPrice = $lastPrice->getBidPrice();
            if ($lastBidPrice <= $stopLoss) {
                $this->logger->debug('Stop loss reach.');
                $this->createEvent($assetPair, $lastPrice, EventEnum::STOP_LOSS);
                continue;
            }

            $latestTopPrice = $lastPrice;
            foreach ($prices as $price) {
                if ($latestTopPrice->getBidPrice() < $price->getBidPrice()) {
                    $latestTopPrice = $price;
                }
            }

            // if the latest price is under the latest top price - 10% => break event
            $topBidPrice = $latestTopPrice->getBidPrice();
            if ($lastBidPrice <= $topBidPrice - ($topBidPrice * (self::BREAK_EVENT_PERCENT / 100))) {
                $this->logger->debug('Break event reach.');
                $this->createEvent($assetPair, $lastPrice, EventEnum::BREAK_EVENT);
            }
        }

        return null;
    }

    /**
     * @param AssetPair $assetPair
     * @param AssetPairPrice $price
     * @param string $eventName
     * @throws ItemReadException
     * @throws NotificationHandlerException
     */
    private function createEvent(AssetPair $assetPair, AssetPairPrice $price, string $eventName): void
    {
        $asset = $this->assetRepository->findOneByAssetPairId($assetPair->getId());
        $event = $this->eventTransactionBuilder->build(
            $eventName,
            $asset,
            $assetPair,
            $price
        );
        $this->transactionAnalyzerNotificationHandler->dispatch($event);
    }

}
