<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Process\Binance\BinanceOrderAnalyzerProcess;
use App\Lambda\BinanceOrderAnalyzerLambda;
use Test\TestCase;

class BinanceOrderAnalyzerLambdaTest extends TestCase
{
    private BinanceOrderAnalyzerLambda $binanceOrderAnalyzerLambda;

    private BinanceOrderAnalyzerProcess $binanceOrderAnalyzerProcess;

    public function setUp(): void
    {
        $this->binanceOrderAnalyzerProcess = $this->createMock(BinanceOrderAnalyzerProcess::class);

        $this->binanceOrderAnalyzerLambda = new BinanceOrderAnalyzerLambda(
            $this->binanceOrderAnalyzerProcess
        );
    }

    /**
     * @throws ItemReadException
     * @throws NotificationHandlerException
     */
    public function testInvokeShouldBeOK(): void
    {
        $this->binanceOrderAnalyzerProcess->expects($this->once())
            ->method('handle');

        $this->binanceOrderAnalyzerLambda->__invoke([]);
    }
}
