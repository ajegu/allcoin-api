<?php


namespace Test\AllCoin\Process\Binance;


use AllCoin\Builder\EventTransactionBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Model\EventEnum;
use AllCoin\Model\EventTransaction;
use AllCoin\Model\Transaction;
use AllCoin\Notification\Handler\TransactionAnalyzerNotificationHandler;
use AllCoin\Process\Binance\BinanceTransactionAnalyzerProcess;
use AllCoin\Repository\AssetPairPriceRepositoryInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use DateTime;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class BinanceTransactionAnalyzerProcessTest extends TestCase
{
    private BinanceTransactionAnalyzerProcess $binanceTransactionAnalyzerProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private AssetPairPriceRepositoryInterface $assetPairPriceRepository;
    private DateTimeService $dateTimeService;
    private TransactionAnalyzerNotificationHandler $transactionAnalyzerNotificationHandler;
    private EventTransactionBuilder $eventTransactionBuilder;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->assetPairPriceRepository = $this->createMock(AssetPairPriceRepositoryInterface::class);
        $this->dateTimeService = $this->createMock(DateTimeService::class);
        $this->transactionAnalyzerNotificationHandler = $this->createMock(TransactionAnalyzerNotificationHandler::class);
        $this->eventTransactionBuilder = $this->createMock(EventTransactionBuilder::class);

        $this->binanceTransactionAnalyzerProcess = new BinanceTransactionAnalyzerProcess(
            $this->assetRepository,
            $this->assetPairRepository,
            $this->assetPairPriceRepository,
            $this->createMock(LoggerInterface::class),
            $this->dateTimeService,
            $this->transactionAnalyzerNotificationHandler,
            $this->eventTransactionBuilder,
        );
    }

    /**
     * @throws ItemReadException
     * @throws NotificationHandlerException
     */
    public function testHandleWithNoBuyTransactionShouldStop(): void
    {
        $lastTransaction = $this->createMock(Transaction::class);
        $lastTransaction->expects($this->once())->method('getDirection')->willReturn(Transaction::SELL);
        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())->method('getLastTransaction')->willReturn($lastTransaction);

        $this->assetPairRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$assetPair]);

        $this->dateTimeService->expects($this->never())->method('now');
        $this->assetPairPriceRepository->expects($this->never())->method('findAllByDateRange');
        $this->assetRepository->expects($this->never())->method('findOneByAssetPairId');
        $this->eventTransactionBuilder->expects($this->never())->method('build');
        $this->transactionAnalyzerNotificationHandler->expects($this->never())->method('dispatch');

        $this->binanceTransactionAnalyzerProcess->handle();
    }

    /**
     * @throws ItemReadException
     * @throws NotificationHandlerException
     */
    public function testHandleWithNoPriceHistoryShouldStop(): void
    {
        $lastTransaction = $this->createMock(Transaction::class);
        $lastTransaction->expects($this->once())->method('getDirection')->willReturn(Transaction::BUY);
        $createdAt = new DateTime();
        $lastTransaction->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);

        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())->method('getLastTransaction')->willReturn($lastTransaction);
        $assetPairId = 'foo';
        $assetPair->expects($this->once())->method('getId')->willReturn($assetPairId);

        $this->assetPairRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$assetPair]);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $this->assetPairPriceRepository->expects($this->once())
            ->method('findAllByDateRange')
            ->with($assetPairId, $createdAt, $now)
            ->willReturn([]);


        $this->assetRepository->expects($this->never())->method('findOneByAssetPairId');
        $this->eventTransactionBuilder->expects($this->never())->method('build');
        $this->transactionAnalyzerNotificationHandler->expects($this->never())->method('dispatch');

        $this->binanceTransactionAnalyzerProcess->handle();
    }

    /**
     * @throws ItemReadException
     * @throws NotificationHandlerException
     */
    public function testHandleWithStopLossShouldSendEvent(): void
    {
        $lastTransaction = $this->createMock(Transaction::class);
        $lastTransaction->expects($this->once())->method('getDirection')->willReturn(Transaction::BUY);
        $createdAt = new DateTime();
        $lastTransaction->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);
        $amount = 10.;
        $lastTransaction->expects($this->once())->method('getAmount')->willReturn($amount);
        $quantity = 5.;
        $lastTransaction->expects($this->once())->method('getQuantity')->willReturn($quantity);

        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())->method('getLastTransaction')->willReturn($lastTransaction);
        $assetPairId = 'foo';
        $assetPair->expects($this->exactly(2))->method('getId')->willReturn($assetPairId);

        $this->assetPairRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$assetPair]);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $lastPrice = $this->createMock(AssetPairPrice::class);
        $bidPrice = 1.;
        $lastPrice->expects($this->once())->method('getBidPrice')->willReturn($bidPrice);

        $this->assetPairPriceRepository->expects($this->once())
            ->method('findAllByDateRange')
            ->with($assetPairId, $createdAt, $now)
            ->willReturn([$lastPrice]);

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneByAssetPairId')
            ->with($assetPairId)
            ->willReturn($asset);

        $event = $this->createMock(EventTransaction::class);
        $this->eventTransactionBuilder->expects($this->once())
            ->method('build')
            ->with(
                EventEnum::STOP_LOSS,
                $asset,
                $assetPair,
                $lastPrice
            )
            ->willReturn($event);


        $this->transactionAnalyzerNotificationHandler->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $this->binanceTransactionAnalyzerProcess->handle();
    }

    /**
     * @throws ItemReadException
     * @throws NotificationHandlerException
     */
    public function testHandleWithBreakEventShouldSendEvent(): void
    {
        $lastTransaction = $this->createMock(Transaction::class);
        $lastTransaction->expects($this->once())->method('getDirection')->willReturn(Transaction::BUY);
        $createdAt = new DateTime();
        $lastTransaction->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);
        $amount = 10.;
        $lastTransaction->expects($this->once())->method('getAmount')->willReturn($amount);
        $quantity = 5.;
        $lastTransaction->expects($this->once())->method('getQuantity')->willReturn($quantity);

        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())->method('getLastTransaction')->willReturn($lastTransaction);
        $assetPairId = 'foo';
        $assetPair->expects($this->exactly(2))->method('getId')->willReturn($assetPairId);

        $this->assetPairRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$assetPair]);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $topPrice = $this->createMock(AssetPairPrice::class);
        $bidPrice = 3.;
        $topPrice->expects($this->exactly(3))->method('getBidPrice')->willReturn($bidPrice);

        $lastPrice = $this->createMock(AssetPairPrice::class);
        $bidPrice = 2.;
        $lastPrice->expects($this->exactly(3))->method('getBidPrice')->willReturn($bidPrice);

        $this->assetPairPriceRepository->expects($this->once())
            ->method('findAllByDateRange')
            ->with($assetPairId, $createdAt, $now)
            ->willReturn([$topPrice, $lastPrice]);

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneByAssetPairId')
            ->with($assetPairId)
            ->willReturn($asset);

        $event = $this->createMock(EventTransaction::class);
        $this->eventTransactionBuilder->expects($this->once())
            ->method('build')
            ->with(
                EventEnum::BREAK_EVENT,
                $asset,
                $assetPair,
                $lastPrice
            )
            ->willReturn($event);


        $this->transactionAnalyzerNotificationHandler->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $this->binanceTransactionAnalyzerProcess->handle();
    }
}
