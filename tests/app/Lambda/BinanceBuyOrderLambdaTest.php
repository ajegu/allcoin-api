<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\EventPriceMapper;
use AllCoin\Model\EventPrice;
use AllCoin\Process\Binance\BinanceBuyOrderProcess;
use App\Lambda\BinanceBuyOrderLambda;
use Test\TestCase;

class BinanceBuyOrderLambdaTest extends TestCase
{
    private BinanceBuyOrderLambda $binanceBuyOrderLambda;

    private BinanceBuyOrderProcess $binanceBuyOrderProcess;
    private EventPriceMapper $eventPriceMapper;

    public function setUp(): void
    {
        $this->binanceBuyOrderProcess = $this->createMock(BinanceBuyOrderProcess::class);
        $this->eventPriceMapper = $this->createMock(EventPriceMapper::class);

        $this->binanceBuyOrderLambda = new BinanceBuyOrderLambda(
            $this->binanceBuyOrderProcess,
            $this->eventPriceMapper,
        );
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testInvokeShouldBeOK(): void
    {
        $message = 'foo';

        $event = [
            'Message' => $message
        ];

        $eventPrice = $this->createMock(EventPrice::class);

        $this->eventPriceMapper->expects($this->once())
            ->method('mapJsonToEvent')
            ->with($message)
            ->willReturn($eventPrice);

        $this->binanceBuyOrderProcess->expects($this->once())
            ->method('handle')
            ->with($eventPrice);

        $this->binanceBuyOrderLambda->__invoke($event);
    }
}
