<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\AssetPair;

interface AssetPairRepositoryInterface
{
    /**
     * @param AssetPair $assetPair
     * @throws ItemSaveException
     */
    public function save(AssetPair $assetPair): void;
}
