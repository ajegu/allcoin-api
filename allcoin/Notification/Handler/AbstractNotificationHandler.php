<?php


namespace AllCoin\Notification\Handler;


use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Model\EventInterface;

abstract class AbstractNotificationHandler implements NotificationHandlerInterface
{
    public function __construct(
        private string $topicArn,
        private SnsHandler $snsHandler
    )
    {
    }

    /**
     * @param EventInterface $event
     * @throws NotificationHandlerException
     */
    public function dispatch(EventInterface $event): void
    {
        $this->snsHandler->publish($event, $this->topicArn);
    }
}
