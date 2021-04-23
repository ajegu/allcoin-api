<?php


namespace Test\AllCoin\Process\Binance;


use AllCoin\Builder\TransactionBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\EventPrice;
use AllCoin\Model\Transaction;
use AllCoin\Process\Binance\BinanceBuyOrderProcess;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\TransactionRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class BinanceBuyOrderProcessTest extends TestCase
{
    private BinanceBuyOrderProcess $binanceBuyOrderProcess;

    private TransactionBuilder $transactionBuilder;
    private TransactionRepositoryInterface $transactionRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->transactionBuilder = $this->createMock(TransactionBuilder::class);
        $this->transactionRepository = $this->createMock(TransactionRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->binanceBuyOrderProcess = new BinanceBuyOrderProcess(
            $this->transactionBuilder,
            $this->transactionRepository,
            $this->assetPairRepository,
            $this->logger,
        );
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testHandleWithExistingTransactionShouldStop(): void
    {
        $transaction = $this->createMock(Transaction::class);
        $transaction->expects($this->once())->method('getDirection')->willReturn(Transaction::BUY);

        $assetPairId = 'foo';
        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())->method('getId')->willReturn($assetPairId);
        $assetPair->expects($this->any())->method('getLastTransaction')->willReturn($transaction);

        $dto = $this->createMock(EventPrice::class);
        $dto->expects($this->once())->method('getAssetPair')->willReturn($assetPair);

        $this->assetPairRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetPairId)
            ->willReturn($assetPair);

        $this->logger->expects($this->never())->method('error');
        $this->transactionBuilder->expects($this->never())->method('build');
        $this->transactionRepository->expects($this->never())->method('save');
        $this->assetPairRepository->expects($this->never())->method('save');

        $this->binanceBuyOrderProcess->handle($dto);
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testHandleShouldBeOK(): void
    {
        $transaction = $this->createMock(Transaction::class);
        $transaction->expects($this->once())->method('getDirection')->willReturn(Transaction::SELL);

        $assetPairId = 'foo';
        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())->method('getId')->willReturn($assetPairId);
        $assetPair->expects($this->once())->method('getLastTransaction')->willReturn($transaction);

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

        $transaction = $this->createMock(Transaction::class);
        $quantity = BinanceBuyOrderProcess::FIXED_TRANSACTION_AMOUNT / $price;
        $this->transactionBuilder->expects($this->once())
            ->method('build')
            ->with(
                $quantity,
                BinanceBuyOrderProcess::FIXED_TRANSACTION_AMOUNT,
                Transaction::BUY,
                $name
            )
            ->willReturn($transaction);

        $this->transactionRepository->expects($this->once())
            ->method('save')
            ->with($transaction, $assetPairId);

        $assetPair->expects($this->once())
            ->method('setLastTransaction')
            ->with($transaction);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($assetPair);

        $this->logger->expects($this->never())->method('error');

        $this->binanceBuyOrderProcess->handle($dto);
    }
}
