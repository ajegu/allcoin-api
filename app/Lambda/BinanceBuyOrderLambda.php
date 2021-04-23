<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\EventPriceMapper;
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
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function __invoke(array $event)
    {
        $eventPrice = $this->eventPriceMapper->mapJsonToEvent($event['Message']);
        $this->binanceBuyOrderProcess->handle($eventPrice);
    }
}
