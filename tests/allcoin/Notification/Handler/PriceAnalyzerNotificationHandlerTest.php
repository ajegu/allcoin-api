<?php


namespace Test\AllCoin\Notification\Handler;


use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Model\EventInterface;
use AllCoin\Notification\Handler\PriceAnalyzerNotificationHandler;
use AllCoin\Notification\Handler\SnsHandler;
use Test\TestCase;

class PriceAnalyzerNotificationHandlerTest extends TestCase
{
    private PriceAnalyzerNotificationHandler $priceAnalyzerNotificationHandler;

    private string $topicArn;
    private SnsHandler $snsHandler;

    public function setUp(): void
    {
        $this->topicArn = 'foo';
        $this->snsHandler = $this->createMock(SnsHandler::class);

        $this->priceAnalyzerNotificationHandler = new PriceAnalyzerNotificationHandler(
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

        $this->priceAnalyzerNotificationHandler->dispatch($event);
    }
}
