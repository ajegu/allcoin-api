<?php


namespace App\Lambda;


use AllCoin\Exception\AssetPairPrice\AssetPairPriceAnalyzerException;
use AllCoin\Process\AssetPairPrice\AssetPairPriceAnalyzerProcess;

class PriceAnalyzerLambda
{
    public function __construct(
        private AssetPairPriceAnalyzerProcess $assetPairPriceAnalyzerProcess
    )
    {
    }

    /**
     * @param array $event
     * @throws AssetPairPriceAnalyzerException
     */
    public function __invoke(array $event): void
    {
        $this->assetPairPriceAnalyzerProcess->handle();
    }
}
