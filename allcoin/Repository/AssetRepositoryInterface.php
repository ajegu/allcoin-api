<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\Asset;

interface AssetRepositoryInterface
{
    /**
     * @return Asset[]
     * @throws ItemReadException
     */
    public function findAll(): array;

    /**
     * @param string $assetId
     * @return Asset
     * @throws ItemReadException
     */
    public function findOneById(string $assetId): Asset;

    /**
     * @param Asset $asset
     * @throws ItemSaveException
     */
    public function save(Asset $asset): void;

    /**
     * @param string $assetId
     * @throws ItemDeleteException
     */
    public function delete(string $assetId);
}
