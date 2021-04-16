<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\AssetPair;
use AllCoin\Model\ModelInterface;

interface AssetPairRepositoryInterface
{
    /**
     * @param AssetPair $assetPair
     * @throws ItemSaveException
     */
    public function save(AssetPair $assetPair): void;

    /**
     * @param string $assetPairId
     * @return AssetPair|ModelInterface
     * @throws ItemReadException
     */
    public function findOneById(string $assetPairId): AssetPair|ModelInterface;

    /**
     * @param string $assetId
     * @return AssetPair[]
     * @throws ItemReadException
     */
    public function findAllByAssetId(string $assetId): array;
}
