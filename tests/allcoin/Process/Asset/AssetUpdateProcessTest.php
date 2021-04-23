<?php


namespace Test\AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\AssetRequestDto;
use AllCoin\Dto\AssetResponseDto;
use AllCoin\Exception\RequiredParameterException;
use AllCoin\Model\Asset;
use AllCoin\Process\Asset\AssetUpdateProcess;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use DateTime;
use Test\TestCase;

class AssetUpdateProcessTest extends TestCase
{
    private AssetUpdateProcess $assetUpdateProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetMapper $assetMapper;
    private DateTimeService $dateTimeService;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetMapper = $this->createMock(AssetMapper::class);
        $this->dateTimeService = $this->createMock(DateTimeService::class);

        $this->assetUpdateProcess = new AssetUpdateProcess(
            $this->assetRepository,
            $this->assetMapper,
            $this->dateTimeService,
        );
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetRequestDto::class);
        $params = [];

        $this->expectException(RequiredParameterException::class);

        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->dateTimeService->expects($this->never())->method('now');
        $this->assetRepository->expects($this->never())->method('save');
        $this->assetMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetUpdateProcess->handle($requestDto, $params);
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testHandleShouldBeOK(): void
    {
        $requestDto = $this->createMock(AssetRequestDto::class);
        $name = 'bar';
        $requestDto->expects($this->once())->method('getName')->willReturn($name);

        $assetId = 'foo';
        $params = ['id' => $assetId];

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $asset->expects($this->once())
            ->method('setName')
            ->with($name);

        $asset->expects($this->once())
            ->method('setUpdatedAt')
            ->with($now);

        $this->assetRepository->expects($this->once())
            ->method('save')
            ->with($asset);

        $this->assetMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($asset)
            ->willReturn($this->createMock(AssetResponseDto::class));

        $this->assetUpdateProcess->handle($requestDto, $params);
    }
}
