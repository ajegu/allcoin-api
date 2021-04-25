<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\Order;

interface OrderRepositoryInterface
{
    /**
     * @param Order $order
     * @param string $assetPairId
     * @throws ItemSaveException
     */
    public function save(Order $order, string $assetPairId): void;

    /**
     * @return array<Order[]>
     * @throws ItemReadException
     */
    public function findAllGroupByAssetPairId(): array;
}
