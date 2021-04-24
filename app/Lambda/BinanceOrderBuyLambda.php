<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\EventPriceMapper;
use AllCoin\Process\Binance\BinanceOrderBuyProcess;

class BinanceOrderBuyLambda implements LambdaInterface
{
    public function __construct(
        private BinanceOrderBuyProcess $binanceBuyOrderProcess,
        private EventPriceMapper $eventPriceMapper
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
        $eventPrice = $this->eventPriceMapper->mapJsonToEvent($event['Message']);
        $this->binanceBuyOrderProcess->handle($eventPrice);
    }
}
