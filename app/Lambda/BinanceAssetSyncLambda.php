<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Process\Binance\BinanceAssetSyncProcess;
use Psr\Http\Client\ClientExceptionInterface;

class BinanceAssetSyncLambda implements LambdaInterface
{
    public function __construct(
        private BinanceAssetSyncProcess $binanceSyncAssetProcess
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
