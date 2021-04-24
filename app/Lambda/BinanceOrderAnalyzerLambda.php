<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Process\Binance\BinanceOrderAnalyzerProcess;

class BinanceOrderAnalyzerLambda implements LambdaInterface
{
    public function __construct(
        private BinanceOrderAnalyzerProcess $binanceOrderAnalyzerProcess
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
        $this->binanceOrderAnalyzerProcess->handle();
    }

}
