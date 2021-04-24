<?php


namespace AllCoin\DataMapper;


use AllCoin\Model\EventInterface;

interface EventMapperInterface
{
    /**
     * @param string $data
     * @return EventInterface
     */
    public function mapJsonToEvent(string $data): EventInterface;
}
