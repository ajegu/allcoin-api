<?php


namespace App\Lambda;


use AllCoin\Exception\Binance\BinanceSyncAssetException;
use AllCoin\Process\Binance\BinanceSyncAssetProcess;

class BinanceAssetSyncLambda
{
    public function __construct(
        private BinanceSyncAssetProcess $binanceSyncAssetProcess
    )
    {
    }

    /**
     * @param array $event
     * @throws BinanceSyncAssetException
     */
    public function __invoke(array $event): void
    {
        $this->binanceSyncAssetProcess->handle();
    }
}
