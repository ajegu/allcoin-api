<?php


namespace Test\App\Lambda;


use AllCoin\Exception\AssetPairPrice\AssetPairPriceBinanceCreateException;
use AllCoin\Process\AssetPairPrice\AssetPairPriceBinanceCreateProcess;
use App\Lambda\GetBinancePriceLambda;
use Test\TestCase;

class GetBinancePriceCommandTest extends TestCase
{
    private GetBinancePriceLambda $getBinancePriceCommand;

    private AssetPairPriceBinanceCreateProcess $assetPairPriceBinanceCreateProcess;

    public function setUp(): void
    {
        $this->assetPairPriceBinanceCreateProcess = $this->createMock(AssetPairPriceBinanceCreateProcess::class);

        $this->getBinancePriceCommand = new GetBinancePriceLambda(
            $this->assetPairPriceBinanceCreateProcess
        );
    }

    /**
     * @throws AssetPairPriceBinanceCreateException
     */
    public function testInvokeShouldBeOK(): void
    {
        $this->assetPairPriceBinanceCreateProcess->expects($this->once())
            ->method('handle');

        $this->getBinancePriceCommand->__invoke([]);
    }
}
