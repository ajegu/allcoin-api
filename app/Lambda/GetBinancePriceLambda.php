<?php


namespace App\Lambda;


use AllCoin\Exception\AssetPairPrice\AssetPairPriceBinanceCreateException;
use AllCoin\Process\AssetPairPrice\AssetPairPriceBinanceCreateProcess;

class GetBinancePriceLambda
{
    public function __construct(
        private AssetPairPriceBinanceCreateProcess $assetPairPriceBinanceCreateProcess
    )
    {
    }

    /**
     * @param array $event
     * @throws AssetPairPriceBinanceCreateException
     */
    public function __invoke(array $event): void
    {
        $this->assetPairPriceBinanceCreateProcess->handle();
    }
}
