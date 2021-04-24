<?php


namespace Test\AllCoin\Process\Binance;


use AllCoin\Builder\OrderBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\EventPrice;
use AllCoin\Model\Order;
use AllCoin\Process\Binance\BinanceBuyOrderProcess;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class BinanceBuyOrderProcessTest extends TestCase
{
    private BinanceBuyOrderProcess $binanceBuyOrderProcess;

    private OrderBuilder $orderBuilder;
    private OrderRepositoryInterface $orderRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->orderBuilder = $this->createMock(OrderBuilder::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->binanceBuyOrderProcess = new BinanceBuyOrderProcess(
            $this->orderBuilder,
            $this->orderRepository,
            $this->assetPairRepository,
            $this->logger,
        );
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testHandleWithExistingOrderShouldStop(): void
    {
        $order = $this->createMock(Order::class);
        $order->expects($this->once())->method('getDirection')->willReturn(Order::BUY);

        $assetPairId = 'foo';
        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())->method('getId')->willReturn($assetPairId);
        $assetPair->expects($this->any())->method('getLastOrder')->willReturn($order);

        $dto = $this->createMock(EventPrice::class);
        $dto->expects($this->once())->method('getAssetPair')->willReturn($assetPair);

        $this->assetPairRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetPairId)
            ->willReturn($assetPair);

        $this->logger->expects($this->never())->method('error');
        $this->orderBuilder->expects($this->never())->method('build');
        $this->orderRepository->expects($this->never())->method('save');
        $this->assetPairRepository->expects($this->never())->method('save');

        $this->binanceBuyOrderProcess->handle($dto);
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testHandleShouldBeOK(): void
    {
        $order = $this->createMock(Order::class);
        $order->expects($this->once())->method('getDirection')->willReturn(Order::SELL);

        $assetPairId = 'foo';
        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())->method('getId')->willReturn($assetPairId);
        $assetPair->expects($this->once())->method('getLastOrder')->willReturn($order);

        $dto = $this->createMock(EventPrice::class);
        $dto->expects($this->once())->method('getAssetPair')->willReturn($assetPair);
        $price = 10.;
        $dto->expects($this->once())->method('getPrice')->willReturn($price);
        $asset = $this->createMock(Asset::class);
        $assetId = 'foo';
        $asset->expects($this->once())->method('getId')->willReturn($assetId);
        $dto->expects($this->once())->method('getAsset')->willReturn($asset);
        $name = 'foo';
        $dto->expects($this->once())->method('getName')->willReturn($name);

        $this->assetPairRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetPairId)
            ->willReturn($assetPair);

        $order = $this->createMock(Order::class);
        $quantity = BinanceBuyOrderProcess::FIXED_TRANSACTION_AMOUNT / $price;
        $this->orderBuilder->expects($this->once())
            ->method('build')
            ->with(
                $quantity,
                BinanceBuyOrderProcess::FIXED_TRANSACTION_AMOUNT,
                Order::BUY,
                $name
            )
            ->willReturn($order);

        $this->orderRepository->expects($this->once())
            ->method('save')
            ->with($order, $assetPairId);

        $assetPair->expects($this->once())
            ->method('setLastOrder')
            ->with($order);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($assetPair);

        $this->logger->expects($this->never())->method('error');

        $this->binanceBuyOrderProcess->handle($dto);
    }
}
