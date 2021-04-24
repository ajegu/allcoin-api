<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\EventPriceMapper;
use AllCoin\Process\Binance\BinanceOrderBuyProcess;
use Psr\Log\LoggerInterface;

class BinanceOrderBuyLambda implements LambdaInterface
{
    public function __construct(
        private BinanceOrderBuyProcess $binanceBuyOrderProcess,
        private EventPriceMapper $eventPriceMapper,
        private LoggerInterface $logger
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
        $this->logger->debug('Receive event', [
            'event' => $event
        ]);
        $eventPrice = $this->eventPriceMapper->mapJsonToEvent($event['Message']);
        $this->binanceBuyOrderProcess->handle($eventPrice);
    }
}
