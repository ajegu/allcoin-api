<?php


namespace Test\AllCoin\Process\AssetPair;


use AllCoin\Builder\AssetPairBuilder;
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
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetPairCreateProcessTest extends TestCase
{
    private AssetPairCreateProcess $assetPairCreateProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private LoggerInterface $logger;
    private AssetPairMapper $assetPairMapper;
    private AssetPairBuilder $assetPairBuilder;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->assetPairMapper = $this->createMock(AssetPairMapper::class);
        $this->assetPairBuilder = $this->createMock(AssetPairBuilder::class);

        $this->assetPairCreateProcess = new AssetPairCreateProcess(
            $this->assetRepository,
            $this->assetPairRepository,
            $this->logger,
            $this->assetPairMapper,
            $this->assetPairBuilder,
        );
    }

    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $params = [];

        $this->expectException(AssetPairCreateException::class);

        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->assetPairBuilder->expects($this->never())->method('build');
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

        $this->assetPairBuilder->expects($this->never())->method('build');
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
        $asset->expects($this->once())->method('getId')->willReturn($assetId);

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $assetPair = $this->createMock(AssetPair::class);
        $this->assetPairBuilder->expects($this->once())
            ->method('build')
            ->with($name)
            ->willReturn($assetPair);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($assetPair, $assetId)
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
        $asset->expects($this->once())->method('getId')->willReturn($assetId);

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $assetPair = $this->createMock(AssetPair::class);
        $this->assetPairBuilder->expects($this->once())
            ->method('build')
            ->with($name)
            ->willReturn($assetPair);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($assetPair, $assetId);

        $this->assetPairMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($assetPair)
            ->willReturn($this->createMock(AssetPairResponseDto::class));

        $this->logger->expects($this->never())->method('error');

        $this->assetPairCreateProcess->handle($requestDto, $params);
    }
}
