<?php


namespace Test\AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemNotFoundException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\ItemManagerInterface;
use AllCoin\Model\Asset;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Repository\AssetRepository;
use AllCoin\Service\SerializerService;
use Test\TestCase;

class AssetRepositoryTest extends TestCase
{
    private AssetRepository $assetRepository;

    private ItemManagerInterface $itemManager;
    private SerializerService $serializerService;

    public function setUp(): void
    {
        $this->itemManager = $this->createMock(ItemManagerInterface::class);
        $this->serializerService = $this->createMock(SerializerService::class);

        $this->assetRepository = new AssetRepository(
            $this->itemManager,
            $this->serializerService
        );
    }

    /**
     * @throws ItemSaveException
     */
    public function testSaveShouldBeOK(): void
    {
        $asset = $this->createMock(Asset::class);
        $assetId = 'foo';
        $asset->expects($this->once())->method('getId')->willReturn($assetId);
        $assetName = 'bar';
        $asset->expects($this->once())->method('getName')->willReturn($assetName);

        $item = [];
        $this->serializerService->expects($this->once())
            ->method('normalizeModel')
            ->with($asset)
            ->willReturn($item);

        $item[ItemManager::LSI_1] = $assetName;

        $this->itemManager->expects($this->once())
            ->method('save')
            ->with($item, 'asset', $assetId);

        $this->assetRepository->save($asset);
    }

    /**
     * @throws ItemReadException
     */
    public function testFindAllShouldBeOK(): void
    {
        $item = [];
        $items = [
            $item
        ];
        $this->itemManager->expects($this->once())
            ->method('fetchAll')
            ->willReturn($items);

        $this->serializerService->expects($this->once())
            ->method('deserializeToModel')
            ->with($item, Asset::class)
            ->willReturn($this->createMock(Asset::class));

        $this->assetRepository->findAll();

    }

    /**
     * @throws ItemReadException
     */
    public function testFindOneShouldBeOK(): void
    {
        $assetId = 'foo';
        $item = [];
        $this->itemManager->expects($this->once())
            ->method('fetchOne')
            ->with(
                ClassMappingEnum::CLASS_MAPPING[Asset::class],
                $assetId
            )
            ->willReturn($item);

        $this->serializerService->expects($this->once())
            ->method('deserializeToModel')
            ->with($item, Asset::class)
            ->willReturn($this->createMock(Asset::class));

        $this->assetRepository->findOneById($assetId);

    }

    /**
     * @throws ItemDeleteException
     */
    public function testDeleteShouldBeOK(): void
    {
        $assetId = 'foo';

        $this->itemManager->expects($this->once())
            ->method('delete')
            ->with(
                ClassMappingEnum::CLASS_MAPPING[Asset::class],
                $assetId
            );

        $this->assetRepository->delete($assetId);
    }

    /**
     * @throws ItemReadException
     */
    public function testFindOneByNameShouldBeOk(): void
    {
        $assetName = 'foo';

        $item = [];
        $this->itemManager->expects($this->once())
            ->method('fetchOneOnLSI')
            ->with(
                ClassMappingEnum::CLASS_MAPPING[Asset::class],
                ItemManager::LSI_1,
                $assetName
            )
            ->willReturn($item);

        $this->serializerService->expects($this->once())
            ->method('deserializeToModel')
            ->with($item, Asset::class)
            ->willReturn($this->createMock(Asset::class));

        $this->assetRepository->findOneByName($assetName);
    }

    /**
     * @throws ItemReadException
     */
    public function testExistsByNameShouldReturnAsset(): void
    {
        $assetName = 'foo';

        $item = [];
        $this->itemManager->expects($this->once())
            ->method('fetchOneOnLSI')
            ->with(
                ClassMappingEnum::CLASS_MAPPING[Asset::class],
                ItemManager::LSI_1,
                $assetName
            )
            ->willReturn($item);

        $asset = $this->createMock(Asset::class);
        $this->serializerService->expects($this->once())
            ->method('deserializeToModel')
            ->with($item, Asset::class)
            ->willReturn($asset);

        $result = $this->assetRepository->existsByName($assetName);
        $this->assertEquals($asset, $result);
    }

    /**
     * @throws ItemReadException
     */
    public function testExistsByNameShouldReturnNull(): void
    {
        $assetName = 'foo';

        $this->itemManager->expects($this->once())
            ->method('fetchOneOnLSI')
            ->with(
                ClassMappingEnum::CLASS_MAPPING[Asset::class],
                ItemManager::LSI_1,
                $assetName
            )
            ->willThrowException($this->createMock(ItemNotFoundException::class));

        $this->serializerService->expects($this->never())->method('deserializeToModel');

        $result = $this->assetRepository->existsByName($assetName);
        $this->assertNull($result);
    }
}
