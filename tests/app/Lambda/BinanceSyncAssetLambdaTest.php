<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Process\Binance\BinanceSyncAssetProcess;
use App\Lambda\BinanceAssetSyncLambda;
use Psr\Http\Client\ClientExceptionInterface;
use Test\TestCase;

class BinanceSyncAssetLambdaTest extends TestCase
{
    private BinanceAssetSyncLambda $binanceAssetSyncLambda;

    private BinanceSyncAssetProcess $binanceSyncAssetProcess;

    public function setUp(): void
    {
        $this->binanceSyncAssetProcess = $this->createMock(BinanceSyncAssetProcess::class);

        $this->binanceAssetSyncLambda = new BinanceAssetSyncLambda(
            $this->binanceSyncAssetProcess
        );
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     * @throws ClientExceptionInterface
     */
    public function testInvokeShouldBeOK(): void
    {
        $this->binanceSyncAssetProcess->expects($this->once())
            ->method('handle');

        $this->binanceAssetSyncLambda->__invoke([]);
    }
}
