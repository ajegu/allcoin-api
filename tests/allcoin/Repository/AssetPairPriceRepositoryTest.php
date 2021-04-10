<?php


namespace Test\AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManagerInterface;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Repository\AssetPairPriceRepository;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\SerializerService;
use DateTime;
use Test\TestCase;

class AssetPairPriceRepositoryTest extends TestCase
{
    private AssetPairPriceRepository $assetPairPriceRepository;

    private ItemManagerInterface $itemManager;
    private SerializerService $serializerService;
    private DateTimeService $dateTimeService;

    public function setUp(): void
    {
        $this->itemManager = $this->createMock(ItemManagerInterface::class);
        $this->serializerService = $this->createMock(SerializerService::class);
        $this->dateTimeService = $this->createMock(DateTimeService::class);

        $this->assetPairPriceRepository = new AssetPairPriceRepository(
            $this->itemManager,
            $this->serializerService,
            $this->dateTimeService
        );
    }

    public function testSaveWithNoAssetPairShouldThrowException(): void
    {
        $assetPairPrice = $this->createMock(AssetPairPrice::class);
        $assetPairPrice->expects($this->once())->method('getAssetPair')->willReturn(null);

        $this->expectException(ItemSaveException::class);

        $this->serializerService->expects($this->never())->method('normalizeModel');
        $this->dateTimeService->expects($this->never())->method('now');
        $this->itemManager->expects($this->never())->method('save');

        $this->assetPairPriceRepository->save($assetPairPrice);
    }

    /**
     * @throws ItemSaveException
     */
    public function testSaveShouldBeOK(): void
    {
        $assetPair = $this->createMock(AssetPair::class);
        $assetPairId = 'foo';
        $assetPair->expects($this->once())->method('getId')->willReturn($assetPairId);

        $assetPairPrice = $this->createMock(AssetPairPrice::class);
        $assetPairPrice->expects($this->exactly(2))->method('getAssetPair')->willReturn($assetPair);

        $data = ['assetPair' => []];
        $this->serializerService->expects($this->once())
            ->method('normalizeModel')
            ->with($assetPairPrice)
            ->willReturn($data);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $dataExpected = [];
        $this->itemManager->expects($this->once())
            ->method('save')
            ->with(
                $dataExpected,
                ClassMappingEnum::CLASS_MAPPING[AssetPairPrice::class] . '_' . $assetPairId,
                $now->getTimestamp()
            );

        $this->assetPairPriceRepository->save($assetPairPrice);
    }
}
