<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Model\Order;

class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    /**
     * @param Order $order
     * @param string $assetPairId
     * @throws ItemSaveException
     */
    public function save(Order $order, string $assetPairId): void
    {
        $data = $this->serializerService->normalizeModel($order);

        $data[ItemManager::LSI_1] = $assetPairId;
        $data[ItemManager::LSI_2] = $order->getVersion();
        $data[ItemManager::LSI_4] = $order->getCreatedAt()->getTimestamp();

        $this->itemManager->save(
            data: $data,
            partitionKey: ClassMappingEnum::CLASS_MAPPING[Order::class],
            sortKey: $order->getId()
        );
    }
}
