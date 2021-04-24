<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Process\Binance\BinancePriceSyncProcess;
use App\Lambda\BinancePriceSyncLambda;
use Test\TestCase;

class BinancePriceSyncLambdaTest extends TestCase
{
    private BinancePriceSyncLambda $getBinancePriceCommand;

    private BinancePriceSyncProcess $assetPairPriceBinanceCreateProcess;

    public function setUp(): void
    {
        $this->assetPairPriceBinanceCreateProcess = $this->createMock(BinancePriceSyncProcess::class);

        $this->getBinancePriceCommand = new BinancePriceSyncLambda(
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
