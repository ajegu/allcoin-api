<?php


namespace AllCoin\DataMapper;


use AllCoin\Model\EventInterface;
use AllCoin\Model\EventOrder;

class EventOrderMapper extends AbstractDataMapper implements EventMapperInterface
{
    public function mapJsonToEvent(string $data): EventOrder|EventInterface
    {
        return $this->serializerService->deserializeToEvent($data, EventOrder::class);
    }
}
