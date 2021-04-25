<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Process\Order\OrderReportProcess;
use App\Lambda\OrderReportLambda;
use Test\TestCase;

class OrderReportLambdaTest extends TestCase
{
    private OrderReportLambda $orderReportLambda;

    private OrderReportProcess $orderReportProcess;

    public function setUp(): void
    {
        $this->orderReportProcess = $this->createMock(OrderReportProcess::class);

        $this->orderReportLambda = new OrderReportLambda(
            $this->orderReportProcess
        );
    }

    /**
     * @throws ItemReadException
     */
    public function testInvokeShouldBeOK(): void
    {
        $this->orderReportProcess->expects($this->once())->method('handle');

        $this->orderReportLambda->__invoke([]);
    }
}
