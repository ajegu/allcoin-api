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

    /**
     * @param string $partitionKey
     * @return array
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
     */
    public function fetchAll(string $partitionKey): array;

    /**
     * @param string $partitionKey
     * @param string $sortKey
     * @return array
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
     */
    public function fetchOne(string $partitionKey, string $sortKey): array;
}
