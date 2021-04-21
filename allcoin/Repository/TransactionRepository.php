<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Model\Transaction;

class TransactionRepository extends AbstractRepository implements TransactionRepositoryInterface
{
    /**
     * @param Transaction $transaction
     * @param string $assetPairId
     * @throws ItemSaveException
     */
    public function save(Transaction $transaction, string $assetPairId): void
    {
        $data = $this->serializerService->normalizeModel($transaction);

        $data[ItemManager::LSI_1] = $assetPairId;
        $data[ItemManager::LSI_2] = $transaction->getVersion();
        $data[ItemManager::LSI_4] = $transaction->getCreatedAt()->getTimestamp();

        $this->itemManager->save(
            data: $data,
            partitionKey: ClassMappingEnum::CLASS_MAPPING[Transaction::class],
            sortKey: $transaction->getId()
        );
    }
}
