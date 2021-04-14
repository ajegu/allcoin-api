<?php


namespace Test\AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Dto\AssetPairResponseDto;
use AllCoin\Exception\AssetPair\AssetPairGetException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Process\AssetPair\AssetPairGetProcess;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetPairGetProcessTest extends TestCase
{
    private AssetPairGetProcess $assetPairGetProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private LoggerInterface $logger;
    private AssetPairMapper $assetPairMapper;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->assetPairMapper = $this->createMock(AssetPairMapper::class);

        $this->assetPairGetProcess = new AssetPairGetProcess(
            $this->assetRepository,
            $this->assetPairRepository,
            $this->logger,
            $this->assetPairMapper,
        );
    }

    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $params = [];

        $this->expectException(AssetPairGetException::class);

        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->assetPairRepository->expects($this->never())->method('findOneById');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairGetProcess->handle($requestDto, $params);
    }

    public function testHandleWithAssetReadErrorShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(AssetPairGetException::class);

        $this->assetPairRepository->expects($this->never())->method('findOneById');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairGetProcess->handle($requestDto, $params);
    }

    public function testHandleWithNoAssetPairIdShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($this->createMock(Asset::class));

        $this->expectException(AssetPairGetException::class);

        $this->logger->expects($this->never())->method('error');
        $this->assetPairRepository->expects($this->never())->method('findOneById');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairGetProcess->handle($requestDto, $params);
    }

    public function testHandleWithAssetPairReadErrorShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $assetId = 'foo';
        $id = 'bar';
        $params = ['assetId' => $assetId, 'id' => $id];

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($this->createMock(Asset::class));

        $this->assetPairRepository->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(AssetPairGetException::class);

        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairGetProcess->handle($requestDto, $params);
    }

    /**
     * @throws AssetPairGetException
     */
    public function testHandleShouldBeOK(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $assetId = 'foo';
        $id = 'bar';
        $params = ['assetId' => $assetId, 'id' => $id];

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($this->createMock(Asset::class));

        $assetPair = $this->createMock(AssetPair::class);
        $this->assetPairRepository->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willReturn($assetPair);

        $this->assetPairMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($assetPair)
            ->willReturn($this->createMock(AssetPairResponseDto::class));

        $this->logger->expects($this->never())->method('error');

        $this->assetPairGetProcess->handle($requestDto, $params);
    }
}
