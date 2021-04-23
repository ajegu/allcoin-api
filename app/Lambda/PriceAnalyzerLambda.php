<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Exception\NotificationHandlerException;
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
     * @throws ItemReadException
     * @throws NotificationHandlerException
     */
    public function __invoke(array $event): void
    {
        $this->assetPairPriceAnalyzerProcess->handle();
    }
}
