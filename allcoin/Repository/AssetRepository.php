<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemNotFoundException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Model\ModelInterface;

class AssetRepository extends AbstractRepository implements AssetRepositoryInterface
{
    /**
     * @return Asset[]
     * @throws ItemReadException
     */
    public function findAll(): array
    {
        $items = $this->itemManager->fetchAll(
            ClassMappingEnum::CLASS_MAPPING[Asset::class]
        );

        return array_map(function (array $item) {
            return $this->serializerService->deserializeToModel($item, Asset::class);
        }, $items);
    }

    /**
     * @param string $assetId
     * @return Asset|ModelInterface
     * @throws ItemReadException
     */
    public function findOneById(string $assetId): Asset|ModelInterface
    {
        $item = $this->itemManager->fetchOne(
            ClassMappingEnum::CLASS_MAPPING[Asset::class],
            $assetId
        );

        return $this->serializerService->deserializeToModel($item, Asset::class);
    }

    /**
     * @param Asset $asset
     * @throws ItemSaveException
     */
    public function save(Asset $asset): void
    {
        $item = $this->serializerService->normalizeModel($asset);

        $item[ItemManager::LSI_1] = $asset->getName();

        $this->itemManager->save(
            data: $item,
            partitionKey: ClassMappingEnum::CLASS_MAPPING[Asset::class],
            sortKey: $asset->getId()
        );
    }

    /**
     * @param string $assetId
     * @throws ItemDeleteException
     */
    public function delete(string $assetId)
    {
        $this->itemManager->delete(
            ClassMappingEnum::CLASS_MAPPING[Asset::class],
            $assetId
        );
    }

    /**
     * @param string $assetName
     * @return Asset|ModelInterface
     * @throws ItemReadException
     */
    public function findOneByName(string $assetName): Asset|ModelInterface
    {
        $item = $this->itemManager->fetchOneOnLSI(
            ClassMappingEnum::CLASS_MAPPING[Asset::class],
            ItemManager::LSI_1,
            $assetName
        );

        return $this->serializerService->deserializeToModel($item, Asset::class);
    }

    /**
     * @param string $assetName
     * @return Asset|ModelInterface|null;
     * @throws ItemReadException
     */
    public function existsByName(string $assetName): Asset|ModelInterface|null
    {
        try {
            return $this->findOneByName($assetName);
        } catch (ItemNotFoundException) {
            return null;
        }
    }

    /**
     * @param string $assetPairId
     * @return Asset|ModelInterface
     * @throws ItemReadException
     */
    public function findOneByAssetPairId(string $assetPairId): Asset|ModelInterface
    {
        $item = $this->itemManager->fetchOne(
            ClassMappingEnum::CLASS_MAPPING[AssetPair::class],
            $assetPairId
        );

        $assetId = $item[ItemManager::LSI_1];

        return $this->findOneById($assetId);
    }

}
