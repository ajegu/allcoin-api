<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Process\Binance\BinanceSyncAssetProcess;
use Psr\Http\Client\ClientExceptionInterface;

class BinanceAssetSyncLambda
{
    public function __construct(
        private BinanceSyncAssetProcess $binanceSyncAssetProcess
    )
    {
    }

    /**
     * @param array $event
     * @throws ItemReadException
     * @throws ItemSaveException
     * @throws ClientExceptionInterface
     */
    public function __invoke(array $event): void
    {
        $this->binanceSyncAssetProcess->handle();
    }
}
