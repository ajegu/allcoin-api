<?php


namespace Test\App\Console\Commands;


use AllCoin\Process\AssetPairPrice\AssetPairPriceBinanceCreateProcess;
use App\Console\Commands\GetBinancePriceCommand;
use Test\TestCase;

class GetBinancePriceCommandTest extends TestCase
{
    private GetBinancePriceCommand $getBinancePriceCommand;

    private AssetPairPriceBinanceCreateProcess $assetPairPriceBinanceCreateProcess;

    public function setUp(): void
    {
        $this->assetPairPriceBinanceCreateProcess = $this->createMock(AssetPairPriceBinanceCreateProcess::class);

        $this->getBinancePriceCommand = new GetBinancePriceCommand(
            $this->assetPairPriceBinanceCreateProcess
        );
    }

    public function testHandleShouldBeOK(): void
    {
        $this->assetPairPriceBinanceCreateProcess->expects($this->once())
            ->method('handle');

        $this->getBinancePriceCommand->handle();
    }
}
