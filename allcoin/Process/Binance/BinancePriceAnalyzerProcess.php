<?php


namespace AllCoin\Process\Binance;


use AllCoin\Builder\EventPriceBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Model\EventEnum;
use AllCoin\Notification\Handler\PriceAnalyzerNotificationHandler;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairPriceRepositoryInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use Psr\Log\LoggerInterface;

class BinancePriceAnalyzerProcess implements ProcessInterface
{
    const TIME_ANALYTICS = 5; // in minutes
    const ALERT_PERCENT_PRICE_UP = 5;
    const ALERT_PERCENT_PRICE_DOWN = -5;

    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private AssetPairRepositoryInterface $assetPairRepository,
        private AssetPairPriceRepositoryInterface $assetPairPriceRepository,
        private LoggerInterface $logger,
        private DateTimeService $dateTimeService,
        private PriceAnalyzerNotificationHandler $eventHandler,
        private EventPriceBuilder $eventPriceBuilder
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
        $end = $this->dateTimeService->now();
        $start = $this->dateTimeService->sub($end, 'PT' . self::TIME_ANALYTICS . 'M');

        $assets = $this->assetRepository->findAll();

        foreach ($assets as $asset) {
            $assetPairs = $this->assetPairRepository->findAllByAssetId($asset->getId());

            foreach ($assetPairs as $assetPair) {
                $prices = $this->assetPairPriceRepository->findAllByDateRange($assetPair->getId(), $start, $end);

                if (count($prices) === 0) {
                    $this->logger->debug('No prices found.', [
                        'assetPair' => $assetPair
                    ]);
                    continue;
                }
                $oldPrice = $prices[0];
                $newPrice = $prices[count($prices) - 1];

                $evolution = $newPrice->getAskPrice() - $oldPrice->getAskPrice();

                $percent = 0;
                if ($newPrice->getAskPrice() > 0) {
                    $percent = round($evolution / $newPrice->getAskPrice() * 100, 2);
                }

                $this->logger->debug(
                    "{$asset->getName()}{$assetPair->getName()} $percent%",
                    [
                        'old' => $oldPrice->getAskPrice(),
                        'new' => $newPrice->getAskPrice(),
                    ]
                );

                $eventName = null;
                if ($percent >= self::ALERT_PERCENT_PRICE_UP) {
                    $eventName = EventEnum::PRICE_UP;
                } else if ($percent <= self::ALERT_PERCENT_PRICE_DOWN) {
                    $eventName = EventEnum::PRICE_DOWN;
                }

                if ($eventName) {
                    $event = $this->eventPriceBuilder->build(
                        $eventName,
                        $asset,
                        $assetPair,
                        $newPrice,
                        $end,
                        $percent
                    );

                    $this->eventHandler->dispatch($event);
                }

                $this->logger->debug(
                    ($eventName) ? 'New event sent' : 'No event sent'
                );

            }
        }

        return null;
    }
}
