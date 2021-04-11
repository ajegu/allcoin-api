<?php


namespace AllCoin\Database\DynamoDb;


interface ItemManagerInterface
{
    /**
     * @param array $data
     * @param string $partitionKey
     * @param string $sortKey
     * @throws \AllCoin\Database\DynamoDb\Exception\PersistenceException
     */
    public function save(array $data, string $partitionKey, string $sortKey): void;
}
