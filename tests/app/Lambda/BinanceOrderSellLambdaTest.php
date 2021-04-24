<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\EventOrderMapper;
use AllCoin\Model\EventOrder;
use AllCoin\Process\Binance\BinanceOrderSellProcess;
use App\Lambda\BinanceOrderSellLambda;
use Test\TestCase;

class BinanceOrderSellLambdaTest extends TestCase
{
    private BinanceOrderSellLambda $binanceOrderSellLambda;

    private BinanceOrderSellProcess $binanceOrderSellProcess;
    private EventOrderMapper $eventOrderMapper;

    public function setUp(): void
    {
        $this->binanceOrderSellProcess = $this->createMock(BinanceOrderSellProcess::class);
        $this->eventOrderMapper = $this->createMock(EventOrderMapper::class);

        $this->binanceOrderSellLambda = new BinanceOrderSellLambda(
            $this->binanceOrderSellProcess,
            $this->eventOrderMapper,
        );
    }

    /**
     * @throws ItemSaveException
     */
    public function testInvokeShouldBeOk(): void
    {
        $message = 'foo';
        $event = ['Message' => $message];

        $eventOrder = $this->createMock(EventOrder::class);

        $this->eventOrderMapper->expects($this->once())
            ->method('mapJsonToEvent')
            ->with($message)
            ->willReturn($eventOrder);

        $this->binanceOrderSellProcess->expects($this->once())
            ->method('handle')
            ->with($eventOrder);

        $this->binanceOrderSellLambda->__invoke($event);
    }
}
