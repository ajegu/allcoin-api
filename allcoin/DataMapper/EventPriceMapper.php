<?php


namespace AllCoin\DataMapper;


use AllCoin\Model\EventInterface;
use AllCoin\Model\EventPrice;

class EventPriceMapper extends AbstractDataMapper
{
    public function mapJsonToEvent(string $data): EventPrice|EventInterface
    {
        return $this->serializerService->deserializeToEvent($data, EventPrice::class);
    }
}
