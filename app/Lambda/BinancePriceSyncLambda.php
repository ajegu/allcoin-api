<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Process\Binance\BinancePriceSyncProcess;

class BinancePriceSyncLambda implements LambdaInterface
{
    public function __construct(
        private BinancePriceSyncProcess $assetPairPriceBinanceCreateProcess
    )
    {
    }

    /**
     * @param array $event
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function __invoke(array $event): void
    {
        $this->assetPairPriceBinanceCreateProcess->handle();
    }
}
