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

    /**
     * @param string $assetId
     * @return \AllCoin\Model\Asset
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
     */
    public function findOneById(string $assetId): Asset;

    /**
     * @param \AllCoin\Model\Asset $asset
     * @throws \AllCoin\Database\DynamoDb\Exception\PersistenceException
     */
    public function save(Asset $asset): void;

    /**
     * @param string $assetId
     * @throws \AllCoin\Database\DynamoDb\Exception\DeleteException
     */
    public function delete(string $assetId);
}
