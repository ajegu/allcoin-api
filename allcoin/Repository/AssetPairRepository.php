<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Model\AssetPair;
use AllCoin\Model\ClassMappingEnum;

class AssetPairRepository extends AbstractRepository implements AssetPairRepositoryInterface
{
    /**
     * @param AssetPair $assetPair
     * @throws ItemSaveException
     */
    public function save(AssetPair $assetPair): void
    {
        $data = $this->serializerService->normalizeModel($assetPair);
        unset($data['asset']);
        $data[ItemManager::LSI_1] = $assetPair->getAsset()->getId();

        $this->itemManager->save(
            $data,
            ClassMappingEnum::CLASS_MAPPING[AssetPair::class],
            $assetPair->getId()
        );
    }
}
