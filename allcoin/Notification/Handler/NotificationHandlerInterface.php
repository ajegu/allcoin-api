<?php


namespace AllCoin\Notification\Handler;


use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Notification\Event\EventInterface;

interface NotificationHandlerInterface
{
    /**
     * @param EventInterface $event
     * @throws NotificationHandlerException
     */
    public function dispatch(EventInterface $event): void;
}
