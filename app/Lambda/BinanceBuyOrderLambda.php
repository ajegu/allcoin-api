<?php


namespace App\Lambda;


use AllCoin\DataMapper\EventPriceMapper;
use AllCoin\Exception\Binance\BinanceBuyOrderProcessException;
use AllCoin\Process\Binance\BinanceBuyOrderProcess;

class BinanceBuyOrderLambda
{
    public function __construct(
        private BinanceBuyOrderProcess $binanceBuyOrderProcess,
        private EventPriceMapper $eventPriceMapper
    )
    {
    }

    /**
     * @param array $event
     * @throws BinanceBuyOrderProcessException
     */
    public function __invoke(array $event)
    {
        $eventPrice = $this->eventPriceMapper->mapJsonToEvent($event['Message']);
        $this->binanceBuyOrderProcess->handle($eventPrice);
    }
}
