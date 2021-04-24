<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Process\Binance\BinanceSyncPriceProcess;
use App\Lambda\BinanceSyncPriceLambda;
use Test\TestCase;

class BinanceSyncPriceLambdaTest extends TestCase
{
    private BinanceSyncPriceLambda $getBinancePriceCommand;

    private BinanceSyncPriceProcess $assetPairPriceBinanceCreateProcess;

    public function setUp(): void
    {
        $this->assetPairPriceBinanceCreateProcess = $this->createMock(BinanceSyncPriceProcess::class);

        $this->getBinancePriceCommand = new BinanceSyncPriceLambda(
            $this->assetPairPriceBinanceCreateProcess
        );
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testInvokeShouldBeOK(): void
    {
        $this->assetPairPriceBinanceCreateProcess->expects($this->once())
            ->method('handle');

        $this->getBinancePriceCommand->__invoke([]);
    }
}
