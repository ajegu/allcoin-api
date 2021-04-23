<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
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
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function __invoke(array $event): void
    {
        $this->assetPairPriceBinanceCreateProcess->handle();
    }
}
