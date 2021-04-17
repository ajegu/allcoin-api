<?php


namespace AllCoin\Notification\Handler;


use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Notification\Event\EventInterface;
use AllCoin\Service\SerializerService;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Psr\Log\LoggerInterface;

class SnsHandler
{
    public function __construct(
        private SnsClient $snsClient,
        private SerializerService $serializerService,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @param EventInterface $event
     * @param string $topicArn
     * @throws NotificationHandlerException
     */
    public function publish(EventInterface $event, string $topicArn): void
    {
        $args = [
            'Message' => $this->serializerService->serializeObject($event),
            'TopicArn' => $topicArn,
            'MessageAttributes' => [
                'event' => [
                    'DataType' => 'String',
                    'StringValue' => $event->getName()
                ]
            ]
        ];

        try {
            $this->snsClient->publish($args);
        } catch (SnsException $exception) {
            $message = 'The event cannot be sent!';
            $this->logger->error($message, [
                'event' => $event->getName(),
                'topic' => $topicArn,
                'message' => $exception->getMessage()
            ]);
            throw new NotificationHandlerException($message);
        }
    }
}
