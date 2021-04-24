<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Process\Binance\BinanceAssetSyncProcess;
use App\Lambda\BinanceAssetSyncLambda;
use Psr\Http\Client\ClientExceptionInterface;
use Test\TestCase;

class BinanceAssetSyncLambdaTest extends TestCase
{
    private BinanceAssetSyncLambda $binanceAssetSyncLambda;

    private BinanceAssetSyncProcess $binanceSyncAssetProcess;

    public function setUp(): void
    {
        $this->binanceSyncAssetProcess = $this->createMock(BinanceAssetSyncProcess::class);

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
