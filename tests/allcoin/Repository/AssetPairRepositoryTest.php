<?php


namespace Test\AllCoin\Repository;


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

    /**
     * @throws ItemSaveException
     */
    public function testSaveShouldBeOK(): void
    {
        $asset = $this->createMock(Asset::class);
        $assetId = 'bar';
        $asset->expects($this->once())->method('getId')->willReturn($assetId);

        $assetPair = $this->createMock(AssetPair::class);
        $assetPair->expects($this->once())->method('getAsset')->willReturn($asset);
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
}
