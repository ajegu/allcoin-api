<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\EventOrderMapper;
use AllCoin\Process\Binance\BinanceOrderSellProcess;

class BinanceOrderSellLambda implements LambdaInterface
{
    public function __construct(
        private BinanceOrderSellProcess $binanceOrderSellProcess,
        private EventOrderMapper $eventOrderMapper
    )
    {
    }

    /**
     * @param array $event
     * @throws ItemSaveException
     */
    public function __invoke(array $event): void
    {
        $eventOrder = $this->eventOrderMapper->mapJsonToEvent($event['Message']);
        $this->binanceOrderSellProcess->handle($eventOrder);
    }

}
