<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\Asset;
use AllCoin\Model\ClassMappingEnum;

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
     * @return Asset
     * @throws ItemReadException
     */
    public function findOneById(string $assetId): Asset
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

}
