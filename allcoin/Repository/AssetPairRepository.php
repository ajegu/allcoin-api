<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Model\AssetPair;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Model\ModelInterface;

class AssetPairRepository extends AbstractRepository implements AssetPairRepositoryInterface
{
    /**
     * @param AssetPair $assetPair
     * @throws ItemSaveException
     */
    public function save(AssetPair $assetPair): void
    {
        if ($assetPair->getAsset() === null) {
            throw new ItemSaveException('You must defined the asset!');
        }

        $data = $this->serializerService->normalizeModel($assetPair);
        unset($data['asset']);
        $data[ItemManager::LSI_1] = $assetPair->getAsset()->getId();

        $this->itemManager->save(
            $data,
            ClassMappingEnum::CLASS_MAPPING[AssetPair::class],
            $assetPair->getId()
        );
    }

    /**
     * @param string $assetPairId
     * @return AssetPair|ModelInterface
     * @throws ItemReadException
     */
    public function findOneById(string $assetPairId): AssetPair|ModelInterface
    {
        $item = $this->itemManager->fetchOne(
            ClassMappingEnum::CLASS_MAPPING[AssetPair::class],
            $assetPairId
        );

        return $this->serializerService->deserializeToModel(
            $item,
            AssetPair::class
        );
    }
}
