<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Process\Binance\BinanceSyncPriceProcess;

class BinanceSyncPriceLambda
{
    public function __construct(
        private BinanceSyncPriceProcess $assetPairPriceBinanceCreateProcess
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
