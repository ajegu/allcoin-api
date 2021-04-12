<?php


namespace AllCoin\Repository;


use AllCoin\Model\Asset;

interface AssetRepositoryInterface
{
    /**
     * @return Asset[]
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
     */
    public function findAll(): array;

    public function findOneById(string $assetId): Asset;

    /**
     * @param \AllCoin\Model\Asset $asset
     * @throws \AllCoin\Database\DynamoDb\Exception\PersistenceException
     */
    public function save(Asset $asset): void;

    public function delete(string $assetId);
}
