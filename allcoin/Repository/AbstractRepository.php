<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\ItemManagerInterface;
use AllCoin\Service\SerializerService;

abstract class AbstractRepository
{
    public function __construct(
        protected ItemManagerInterface $itemManager,
        protected SerializerService $serializerService
    )
    {
    }
}
