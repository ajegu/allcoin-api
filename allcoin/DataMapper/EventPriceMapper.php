<?php


namespace AllCoin\DataMapper;


use AllCoin\Model\EventPrice;

class EventPriceMapper extends AbstractDataMapper
{
    public function mapJsonToEvent(string $data): EventPrice
    {
        return $this->serializerService->deserializeToEvent($data, EventPrice::class);
    }
}
