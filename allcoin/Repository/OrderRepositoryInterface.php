<?php


namespace AllCoin\Repository;


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
}
