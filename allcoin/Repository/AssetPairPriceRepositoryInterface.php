<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\AssetPairPrice;

interface AssetPairPriceRepositoryInterface
{
    /**
     * @param AssetPairPrice $assetPairPrice
     * @throws ItemSaveException
     */
    public function save(AssetPairPrice $assetPairPrice): void;
}
