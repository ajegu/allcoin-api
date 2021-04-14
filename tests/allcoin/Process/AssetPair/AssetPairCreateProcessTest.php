<?php


namespace Test\AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Dto\AssetPairResponseDto;
use AllCoin\Exception\AssetPair\AssetPairCreateException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Process\AssetPair\AssetPairCreateProcess;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\UuidService;
use DateTime;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetPairCreateProcessTest extends TestCase
{
    private AssetPairCreateProcess $assetPairCreateProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private LoggerInterface $logger;
    private UuidService $uuidService;
    private DateTimeService $dateTimeService;
    private AssetPairMapper $assetPairMapper;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->uuidService = $this->createMock(UuidService::class);
        $this->dateTimeService = $this->createMock(DateTimeService::class);
        $this->assetPairMapper = $this->createMock(AssetPairMapper::class);

        $this->assetPairCreateProcess = new AssetPairCreateProcess(
            $this->assetRepository,
            $this->assetPairRepository,
            $this->logger,
            $this->uuidService,
            $this->dateTimeService,
            $this->assetPairMapper,
        );
    }

    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $params = [];

        $this->expectException(AssetPairCreateException::class);

        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->uuidService->expects($this->never())->method('generateUuid');
        $this->dateTimeService->expects($this->never())->method('now');
        $this->assetPairRepository->expects($this->never())->method('save');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairCreateProcess->handle($requestDto, $params);
    }

    public function testHandleWithReadErrorShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(AssetPairCreateException::class);

        $this->uuidService->expects($this->never())->method('generateUuid');
        $this->dateTimeService->expects($this->never())->method('now');
        $this->assetPairRepository->expects($this->never())->method('save');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairCreateProcess->handle($requestDto, $params);
    }

    public function testHandleWithSaveErrorShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $name = 'foo';
        $requestDto->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $uuid = 'bar';
        $this->uuidService->expects($this->once())
            ->method('generateUuid')
            ->willReturn($uuid);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $assetPair = new AssetPair(
            id: $uuid,
            name: $name,
            createdAt: $now
        );
        $assetPair->setAsset($asset);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($assetPair)
            ->willThrowException($this->createMock(ItemSaveException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(AssetPairCreateException::class);

        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairCreateProcess->handle($requestDto, $params);
    }

    /**
     * @throws AssetPairCreateException
     */
    public function testHandleShouldBeOK(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $name = 'foo';
        $requestDto->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $uuid = 'bar';
        $this->uuidService->expects($this->once())
            ->method('generateUuid')
            ->willReturn($uuid);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $assetPair = new AssetPair(
            id: $uuid,
            name: $name,
            createdAt: $now
        );
        $assetPair->setAsset($asset);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($assetPair);

        $this->assetPairMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($assetPair)
            ->willReturn($this->createMock(AssetPairResponseDto::class));

        $this->logger->expects($this->never())->method('error');

        $this->assetPairCreateProcess->handle($requestDto, $params);
    }
}
