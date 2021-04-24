<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Process\Binance\BinancePriceAnalyzerProcess;

class BinancePriceAnalyzerLambda
{
    public function __construct(
        private BinancePriceAnalyzerProcess $assetPairPriceAnalyzerProcess
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
