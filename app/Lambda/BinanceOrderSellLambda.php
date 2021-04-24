<?php


namespace App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\EventOrderMapper;
use AllCoin\Process\Binance\BinanceOrderSellProcess;
use Psr\Log\LoggerInterface;

class BinanceOrderSellLambda extends AbstractLambda implements LambdaInterface
{
    public function __construct(
        private BinanceOrderSellProcess $binanceOrderSellProcess,
        private EventOrderMapper $eventOrderMapper,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @param array $event
     * @throws ItemSaveException
     */
    public function __invoke(array $event): void
    {
        $this->logger->debug('Receive event', [
            'event' => $event
        ]);
        $message = $this->getMessageFromEvent($event);
        $this->logger->debug('Message extract', [
            'message' => $message
        ]);

        if ($message) {
            $eventOrder = $this->eventOrderMapper->mapJsonToEvent($message);
            $this->binanceOrderSellProcess->handle($eventOrder);
        }
    }

}
