<?php


namespace AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManagerInterface;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\SerializerService;

class AssetPairPriceRepository extends AbstractRepository implements AssetPairPriceRepositoryInterface
{
    public function __construct(
        protected ItemManagerInterface $itemManager,
        protected SerializerService $serializerService,
        private DateTimeService $dateTimeService
    )
    {
        parent::__construct($itemManager, $serializerService);
    }

    /**
     * @param AssetPairPrice $assetPairPrice
     * @throws ItemSaveException
     */
    public function save(AssetPairPrice $assetPairPrice): void
    {
        if ($assetPairPrice->getAssetPair() === null) {
            throw new ItemSaveException('You must defined the asset pair!');
        }

        $data = $this->serializerService->normalizeModel($assetPairPrice);
        unset($data['assetPair']);

        $this->itemManager->save(
            $data,
            ClassMappingEnum::CLASS_MAPPING[AssetPairPrice::class] . '_' . $assetPairPrice->getAssetPair()->getId(),
            $this->dateTimeService->now()->getTimestamp()
        );
    }

}
