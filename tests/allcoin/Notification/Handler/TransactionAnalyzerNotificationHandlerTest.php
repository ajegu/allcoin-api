<?php


namespace Test\AllCoin\Notification\Handler;


use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Model\EventInterface;
use AllCoin\Notification\Handler\SnsHandler;
use AllCoin\Notification\Handler\TransactionAnalyzerNotificationHandler;
use Test\TestCase;

class TransactionAnalyzerNotificationHandlerTest extends TestCase
{
    private TransactionAnalyzerNotificationHandler $transactionAnalyzerNotificationHandler;

    private string $topicArn;
    private SnsHandler $snsHandler;

    public function setUp(): void
    {
        $this->topicArn = 'foo';
        $this->snsHandler = $this->createMock(SnsHandler::class);

        $this->transactionAnalyzerNotificationHandler = new TransactionAnalyzerNotificationHandler(
            $this->topicArn,
            $this->snsHandler
        );
    }

    /**
     * @throws NotificationHandlerException
     */
    public function testDispatchShouldBeOK(): void
    {
        $event = $this->createMock(EventInterface::class);

        $this->snsHandler->expects($this->once())
            ->method('publish')
            ->with($event, $this->topicArn);

        $this->transactionAnalyzerNotificationHandler->dispatch($event);
    }
}
