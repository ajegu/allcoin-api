<?php


namespace Test\AllCoin\Notification\Handler;


use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Model\EventInterface;
use AllCoin\Notification\Handler\SnsHandler;
use AllCoin\Service\SerializerService;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class SnsHandlerTest extends TestCase
{
    private SnsHandler $snsHandler;

    private SnsClient $snsClient;
    private SerializerService $serializerService;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->snsClient = $this->createMock(SnsClient::class);
        $this->serializerService = $this->createMock(SerializerService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->snsHandler = new SnsHandler(
            $this->snsClient,
            $this->serializerService,
            $this->logger,
        );
    }

    public function testPublishWithErrorShouldThrowException(): void
    {
        $event = $this->createMock(EventInterface::class);
        $eventName = 'bar';
        $event->expects($this->exactly(2))->method('getName')->willReturn($eventName);

        $topicArn = 'foo';

        $message = '';
        $this->serializerService->expects($this->once())
            ->method('serializeObject')
            ->with($event)
            ->willReturn($message);

        $args = [
            'Message' => $message,
            'TopicArn' => $topicArn,
            'MessageAttributes' => [
                'event' => [
                    'DataType' => 'String',
                    'StringValue' => $eventName
                ]
            ]
        ];

        $this->snsClient->expects($this->once())
            ->method('__call')
            ->with('publish', [$args])
            ->willThrowException($this->createMock(SnsException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(NotificationHandlerException::class);

        $this->snsHandler->publish($event, $topicArn);
    }

    /**
     * @throws NotificationHandlerException
     */
    public function testPublishShouldBeOK(): void
    {
        $event = $this->createMock(EventInterface::class);
        $eventName = 'bar';
        $event->expects($this->once())->method('getName')->willReturn($eventName);

        $topicArn = 'foo';

        $message = '';
        $this->serializerService->expects($this->once())
            ->method('serializeObject')
            ->with($event)
            ->willReturn($message);

        $args = [
            'Message' => $message,
            'TopicArn' => $topicArn,
            'MessageAttributes' => [
                'event' => [
                    'DataType' => 'String',
                    'StringValue' => $eventName
                ]
            ]
        ];

        $this->snsClient->expects($this->once())
            ->method('__call')
            ->with('publish', [$args]);

        $this->logger->expects($this->never())->method('error');

        $this->snsHandler->publish($event, $topicArn);
    }
}
