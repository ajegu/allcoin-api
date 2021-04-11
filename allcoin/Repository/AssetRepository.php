<?php


namespace AllCoin\Repository;


use AllCoin\Model\Asset;
use BadMethodCallException;

class AssetRepository extends AbstractRepository implements AssetRepositoryInterface
{
    public function findAll(): array
    {
        throw new BadMethodCallException();
    }

    public function findOneById(string $assetId): Asset
    {
        throw new BadMethodCallException();
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
            partitionKey: $asset->getName(),
            sortKey: $asset->getName()
        );
    }

    public function delete(string $assetId)
    {
        throw new BadMethodCallException();
    }

}
