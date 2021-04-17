<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\AssetPairPrice;
use DateTime;

interface AssetPairPriceRepositoryInterface
{
    /**
     * @param AssetPairPrice $assetPairPrice
     * @throws ItemSaveException
     */
    public function save(AssetPairPrice $assetPairPrice): void;

    /**
     * @param string $assetPairId
     * @param DateTime $start
     * @param DateTime $end
     * @return AssetPairPrice[]
     * @throws ItemReadException
     */
    public function findAllByDateRange(string $assetPairId, DateTime $start, DateTime $end): array;
}
