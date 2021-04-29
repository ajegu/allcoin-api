<?php


namespace AllCoin\Process\Binance;


use AllCoin\Builder\EventOrderBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Model\EventEnum;
use AllCoin\Model\Order;
use AllCoin\Notification\Handler\OrderAnalyzerNotificationHandler;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairPriceRepositoryInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use Psr\Log\LoggerInterface;

class BinanceOrderAnalyzerProcess implements ProcessInterface
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private AssetPairRepositoryInterface $assetPairRepository,
        private AssetPairPriceRepositoryInterface $assetPairPriceRepository,
        private LoggerInterface $logger,
        private DateTimeService $dateTimeService,
        private OrderAnalyzerNotificationHandler $orderAnalyzerNotificationHandler,
        private EventOrderBuilder $eventOrderBuilder,
        private int $stopLossPercent,
        private int $breakEventPercent
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

            $lastOrder = $assetPair->getLastOrder();

            if ($lastOrder === null || $lastOrder->getDirection() === Order::SELL) {
                $this->logger->debug('Not a buy order.');
                continue;
            }

            $prices = $this->assetPairPriceRepository->findAllByDateRange(
                $assetPair->getId(),
                $lastOrder->getCreatedAt(),
                $this->dateTimeService->now()
            );

            $lastPrice = $prices[count($prices) - 1] ?? null;
            if (!$lastPrice) {
                $this->logger->debug('No prices found.');
                continue;
            }

            $unitPrice = $lastOrder->getAmount() / $lastOrder->getQuantity();

            $stopLoss = $unitPrice - ($unitPrice * ($this->stopLossPercent / 100));

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
            if ($lastBidPrice <= $topBidPrice - ($topBidPrice * ($this->breakEventPercent / 100))) {
                $this->logger->debug('Break event reach.');
                $this->createEvent($assetPair, $lastPrice, EventEnum::BREAK_EVENT);
            }
        }

        $this->logger->debug('Nothing to do.');

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
        $event = $this->eventOrderBuilder->build(
            $eventName,
            $asset,
            $assetPair,
            $price
        );
        $this->orderAnalyzerNotificationHandler->dispatch($event);
    }

}
