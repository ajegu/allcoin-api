<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\EventPriceMapper;
use AllCoin\Model\EventPrice;
use AllCoin\Process\Binance\BinanceOrderBuyProcess;
use App\Lambda\BinanceOrderBuyLambda;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class BinanceOrderBuyLambdaTest extends TestCase
{
    private BinanceOrderBuyLambda $binanceBuyOrderLambda;

    private BinanceOrderBuyProcess $binanceBuyOrderProcess;
    private EventPriceMapper $eventPriceMapper;

    public function setUp(): void
    {
        $this->binanceBuyOrderProcess = $this->createMock(BinanceOrderBuyProcess::class);
        $this->eventPriceMapper = $this->createMock(EventPriceMapper::class);

        $this->binanceBuyOrderLambda = new BinanceOrderBuyLambda(
            $this->binanceBuyOrderProcess,
            $this->eventPriceMapper,
            $this->createMock(LoggerInterface::class)
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
            'Records' => [
                [
                    'Sns' => [
                        'Message' => $message
                    ]
                ]
            ]
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

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testInvokeWithNoMessageShouldStop(): void
    {
        $event = [];

        $this->eventPriceMapper->expects($this->never())->method('mapJsonToEvent');
        $this->binanceBuyOrderProcess->expects($this->never())->method('handle');

        $this->binanceBuyOrderLambda->__invoke($event);
    }
}
