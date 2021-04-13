<?php


namespace AllCoin\Repository;


use AllCoin\Model\Asset;
use AllCoin\Model\ClassMappingEnum;
use BadMethodCallException;

class AssetRepository extends AbstractRepository implements AssetRepositoryInterface
{
    /**
     * @return Asset[]
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
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
     * @return \AllCoin\Model\Asset
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
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
     * @param \AllCoin\Model\Asset $asset
     * @throws \AllCoin\Database\DynamoDb\Exception\PersistenceException
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

    public function delete(string $assetId)
    {
        throw new BadMethodCallException();
    }

}
