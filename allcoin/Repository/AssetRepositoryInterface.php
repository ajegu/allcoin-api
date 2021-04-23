<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\Asset;
use AllCoin\Model\ModelInterface;

interface AssetRepositoryInterface
{
    /**
     * @return Asset[]
     * @throws ItemReadException
     */
    public function findAll(): array;

    /**
     * @param string $assetId
     * @return Asset|ModelInterface
     * @throws ItemReadException
     */
    public function findOneById(string $assetId): Asset|ModelInterface;

    /**
     * @param Asset $asset
     * @throws ItemSaveException
     */
    public function save(Asset $asset): void;

    /**
     * @param string $assetId
     * @throws ItemDeleteException
     */
    public function delete(string $assetId);

    /**
     * @param string $assetName
     * @return Asset|ModelInterface
     * @throws ItemReadException
     */
    public function findOneByName(string $assetName): Asset|ModelInterface;

    /**
     * @param string $assetName
     * @return Asset|ModelInterface|null;
     * @throws ItemReadException
     */
    public function existsByName(string $assetName): Asset|ModelInterface|null;

    /**
     * @param string $assetPairId
     * @return Asset|ModelInterface
     * @throws ItemReadException
     */
    public function findOneByAssetPairId(string $assetPairId): Asset|ModelInterface;
}
