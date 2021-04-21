<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * @param Transaction $transaction
     * @param string $assetPairId
     * @throws ItemSaveException
     */
    public function save(Transaction $transaction, string $assetPairId): void;
}
