<?php


namespace Test\AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\ItemManagerInterface;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Repository\AssetPairRepository;
use AllCoin\Service\SerializerService;
use Test\TestCase;

class AssetPairRepositoryTest extends TestCase
{
    private AssetPairRepository $assetPairRepository;

    private ItemManagerInterface $itemManager;
    private SerializerService $serializerService;

    public function setUp(): void
    {
        $this->itemManager = $this->createMock(ItemManagerInterface::class);
        $this->serializerService = $this->createMock(SerializerService::class);

        $this->assetPairRepository = new AssetPairRepository(
            $this->itemManager,
            $this->serializerService
        );
    }

    public function testSaveWithNoAssetShouldBeThrowException(): void
    {
        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())
            ->method('getAsset')
            ->willReturn(null);

        $this->expectException(ItemSaveException::class);

        $this->serializerService->expects($this->never())->method('normalizeModel');
        $this->itemManager->expects($this->never())->method('save');

        $this->assetPairRepository->save($assetPair);
    }

    /**
     * @throws ItemSaveException
     */
    public function testSaveShouldBeOK(): void
    {
        $asset = $this->createMock(Asset::class);
        $assetId = 'bar';
        $asset->expects($this->once())->method('getId')->willReturn($assetId);

        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->exactly(2))->method('getAsset')->willReturn($asset);
        $assetPairId = 'foo';
        $assetPair->expects($this->once())->method('getId')->willReturn($assetPairId);

        $item = [
            'asset' => []
        ];
        $this->serializerService->expects($this->once())
            ->method('normalizeModel')
            ->with($assetPair)
            ->willReturn($item);

        $itemExpected = [
            ItemManager::LSI_1 => $assetId
        ];

        $this->itemManager->expects($this->once())
            ->method('save')
            ->with($itemExpected, ClassMappingEnum::CLASS_MAPPING[AssetPair::class], $assetPairId);

        $this->assetPairRepository->save($assetPair);
    }

    /**
     * @throws ItemReadException
     */
    public function testFindOneByIdShouldBeOK(): void
    {
        $assetPairId = 'foo';

        $item = [];
        $this->itemManager->expects($this->once())
            ->method('fetchOne')
            ->with(
                ClassMappingEnum::CLASS_MAPPING[AssetPair::class],
                $assetPairId
            )
            ->willReturn($item);

        $this->serializerService->expects($this->once())
            ->method('deserializeToModel')
            ->with(
                $item,
                AssetPair::class
            )
            ->willReturn($this->createMock(AssetPair::class));

        $this->assetPairRepository->findOneById($assetPairId);
    }

    /**
     * @throws ItemDeleteException
     */
    public function testDeleteShouldBeOK(): void
    {
        $assetPairId = 'foo';

        $this->itemManager->expects($this->once())
            ->method('delete')
            ->with(
                ClassMappingEnum::CLASS_MAPPING[AssetPair::class],
                $assetPairId
            );

        $this->assetPairRepository->delete($assetPairId);
    }

    /**
     * @throws ItemReadException
     */
    public function testFindAllByAssetIdShouldBeOK(): void
    {
        $assetId = 'foo';

        $item = [];
        $items = [$item];
        $this->itemManager->expects($this->once())
            ->method('fetchAllOnLSI')
            ->with(
                partitionKey: ClassMappingEnum::CLASS_MAPPING[AssetPair::class],
                lsiKeyName: ItemManager::LSI_1,
                lsiKey: $assetId,
            )
            ->willReturn($items);

        $this->serializerService->expects($this->once())
            ->method('deserializeToModel')
            ->with($item, AssetPair::class)
            ->willReturn($this->createMock(AssetPair::class));

        $this->assetPairRepository->findAllByAssetId($assetId);
    }
}
