<?php


namespace AllCoin\Process\AssetPairPrice;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\AssetPairPrice\AssetPairPriceAnalyzerException;
use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Notification\Builder\EventPriceBuilder;
use AllCoin\Notification\Event\EventEnum;
use AllCoin\Notification\Event\EventPrice;
use AllCoin\Notification\Handler\PriceAnalyzerNotificationHandler;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairPriceRepositoryInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use DateTime;
use Psr\Log\LoggerInterface;

class AssetPairPriceAnalyzerProcess implements ProcessInterface
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
     * @throws AssetPairPriceAnalyzerException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $end = $this->dateTimeService->now();
        $start = $this->dateTimeService->sub($end, 'PT' . self::TIME_ANALYTICS . 'M');

        $assets = $this->getAssets();

        foreach ($assets as $asset) {
            $assetPairs = $this->getAssetPairs($asset->getId());

            foreach ($assetPairs as $assetPair) {
                $prices = $this->getAssetPairPrices(
                    $assetPair->getId(),
                    $start,
                    $end
                );

                $oldPrice = $prices[0];
                $newPrice = $prices[count($prices) - 1];

                $evolution = $newPrice->getAskPrice() - $oldPrice->getAskPrice();
                $percent = round($evolution / $newPrice->getAskPrice() * 100, 2);

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

                    $this->sendEvent($event);
                }

                $this->logger->debug(
                    ($eventName) ? 'New event sent' : 'No event sent'
                );

            }
        }

        return null;
    }

    /**
     * @return Asset[]
     * @throws AssetPairPriceAnalyzerException
     */
    private function getAssets(): array
    {
        try {
            return $this->assetRepository->findAll();
        } catch (ItemReadException $exception) {
            $message = 'Cannot get the assets for analytics process';
            $this->logger->error($message, [
                'exception' => $exception->getMessage()
            ]);
            throw new AssetPairPriceAnalyzerException($message);
        }
    }

    /**
     * @param string $assetId
     * @return AssetPair[]
     * @throws AssetPairPriceAnalyzerException
     */
    private function getAssetPairs(string $assetId): array
    {
        try {
            return $this->assetPairRepository->findAllByAssetId($assetId);
        } catch (ItemReadException $exception) {
            $message = 'Cannot get the asset pairs for analytics process.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'assetId' => $assetId
            ]);
            throw new AssetPairPriceAnalyzerException($message);
        }
    }

    /**
     * @return AssetPairPrice[]
     * @throws AssetPairPriceAnalyzerException
     */
    private function getAssetPairPrices(string $assetPairId, DateTime $start, DateTime $end): array
    {
        try {
            return $this->assetPairPriceRepository->findAllByDateRange($assetPairId, $start, $end);
        } catch (ItemReadException $exception) {
            $message = 'Cannot get the asset pair prices for analytics process.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'assetPairId' => $assetPairId,
                'start' => $start->format(DATE_RFC3339),
                'end' => $end->format(DATE_RFC3339),
            ]);
            throw new AssetPairPriceAnalyzerException($message);
        }
    }

    /**
     * @param EventPrice $eventPrice
     * @throws AssetPairPriceAnalyzerException
     */
    private function sendEvent(EventPrice $eventPrice): void
    {
        try {
            $this->eventHandler->dispatch($eventPrice);
        } catch (NotificationHandlerException $exception) {
            $message = 'The event cannot be sent during price analyse!';
            $this->logger->error($message, [
                'message' => $exception->getMessage()
            ]);
            throw new AssetPairPriceAnalyzerException($message);
        }
    }
}
