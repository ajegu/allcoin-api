<?php


namespace Test\AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Dto\AssetResponseDto;
use AllCoin\Exception\AssetPair\AssetPairUpdateException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Process\AssetPair\AssetPairUpdateProcess;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use DateTime;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetPairUpdateProcessTest extends TestCase
{
    private AssetPairUpdateProcess $assetPairUpdateProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private LoggerInterface $logger;
    private DateTimeService $dateTimeService;
    private AssetPairMapper $assetPairMapper;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->dateTimeService = $this->createMock(DateTimeService::class);
        $this->assetPairMapper = $this->createMock(AssetPairMapper::class);

        $this->assetPairUpdateProcess = new AssetPairUpdateProcess(
            $this->assetRepository,
            $this->assetPairRepository,
            $this->logger,
            $this->dateTimeService,
            $this->assetPairMapper,
        );
    }

    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $params = [];

        $this->expectException(AssetPairUpdateException::class);

        $this->logger->expects($this->never())->method('error');
        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->dateTimeService->expects($this->never())->method('now');
        $this->assetPairRepository->expects($this->never())->method('save');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairUpdateProcess->handle($requestDto, $params);
    }

    public function testHandleWithNoAssetPairIdShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $this->expectException(AssetPairUpdateException::class);

        $this->logger->expects($this->never())->method('error');
        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->assetPairRepository->expects($this->never())->method('findOneById');
        $this->dateTimeService->expects($this->never())->method('now');
        $this->assetPairRepository->expects($this->never())->method('save');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairUpdateProcess->handle($requestDto, $params);
    }

    public function testHandleWithAssetReadErrorShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $assetId = 'foo';
        $id = 'bar';
        $params = ['assetId' => $assetId, 'id' => $id];

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(AssetPairUpdateException::class);

        $this->assetPairRepository->expects($this->never())->method('findOneById');
        $this->dateTimeService->expects($this->never())->method('now');
        $this->assetPairRepository->expects($this->never())->method('save');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairUpdateProcess->handle($requestDto, $params);
    }

    public function testHandleWithAssetPairReadErrorShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);

        $assetId = 'foo';
        $id = 'bar';
        $params = ['assetId' => $assetId, 'id' => $id];

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $this->assetPairRepository->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(AssetPairUpdateException::class);


        $this->dateTimeService->expects($this->never())->method('now');
        $this->assetPairRepository->expects($this->never())->method('save');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairUpdateProcess->handle($requestDto, $params);
    }

    public function testHandleWithAssetPairSaveErrorShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $name = 'bar';
        $requestDto->expects($this->once())->method('getName')->willReturn($name);

        $assetId = 'foo';
        $id = 'bar';
        $params = ['assetId' => $assetId, 'id' => $id];

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $assetPair = $this->createMock(AssetPair::class);
        $this->assetPairRepository->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willReturn($assetPair);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $assetPair->expects($this->once())->method('setAsset')->with($asset);
        $assetPair->expects($this->once())->method('setName')->with($name);
        $assetPair->expects($this->once())->method('setUpdatedAt')->with($now);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($assetPair)
            ->willThrowException($this->createMock(ItemSaveException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(AssetPairUpdateException::class);

        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairUpdateProcess->handle($requestDto, $params);
    }

    /**
     * @throws AssetPairUpdateException
     */
    public function testHandleShouldBeOK(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $name = 'bar';
        $requestDto->expects($this->once())->method('getName')->willReturn($name);

        $assetId = 'foo';
        $id = 'bar';
        $params = ['assetId' => $assetId, 'id' => $id];

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $assetPair = $this->createMock(AssetPair::class);
        $this->assetPairRepository->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willReturn($assetPair);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($now);

        $assetPair->expects($this->once())->method('setAsset')->with($asset);
        $assetPair->expects($this->once())->method('setName')->with($name);
        $assetPair->expects($this->once())->method('setUpdatedAt')->with($now);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($assetPair);

        $this->assetPairMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($assetPair)
            ->willReturn($this->createMock(AssetResponseDto::class));

        $this->logger->expects($this->never())->method('error');

        $this->assetPairUpdateProcess->handle($requestDto, $params);
    }
}
