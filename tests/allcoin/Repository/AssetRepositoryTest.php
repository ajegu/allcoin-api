<?php


namespace Test\AllCoin\Repository;


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
     * @throws \AllCoin\Database\DynamoDb\Exception\PersistenceException
     */
    public function testSaveShouldBeOK(): void
    {
        $asset = $this->createMock(Asset::class);
        $assetId = 'foo';
        $asset->expects($this->once())->method('getId')->willReturn($assetId);

        $item = [];
        $this->serializerService->expects($this->once())
            ->method('normalizeModel')
            ->with($asset)
            ->willReturn($item);

        $this->itemManager->expects($this->once())
            ->method('save')
            ->with($item, 'asset', $assetId);

        $this->assetRepository->save($asset);
    }

    /**
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
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
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
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
}
