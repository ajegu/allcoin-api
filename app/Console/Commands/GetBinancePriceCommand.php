<?php


namespace App\Console\Commands;

use AllCoin\Exception\AssetPairPrice\AssetPairPriceBinanceCreateException;
use AllCoin\Process\AssetPairPrice\AssetPairPriceBinanceCreateProcess;
use Illuminate\Console\Command;

class GetBinancePriceCommand extends Command
{
    protected $signature = 'price:binance';

    public function __construct(
        private AssetPairPriceBinanceCreateProcess $assetPairPriceBinanceCreateProcess
    )
    {
        parent::__construct();
    }

    /**
     * @throws AssetPairPriceBinanceCreateException
     */
    public function handle(): void
    {
        $this->assetPairPriceBinanceCreateProcess->handle();
    }
}
