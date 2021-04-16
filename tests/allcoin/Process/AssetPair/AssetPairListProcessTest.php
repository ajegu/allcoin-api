<?php


namespace Test\AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Dto\AssetResponseDto;
use AllCoin\Dto\ListResponseDto;
use AllCoin\Exception\AssetPair\AssetPairListException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Process\AssetPair\AssetPairListProcess;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetPairListProcessTest extends TestCase
{
    private AssetPairListProcess $assetPairListProcess;

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

        $this->assetPairListProcess = new AssetPairListProcess(
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

        $this->expectException(AssetPairListException::class);

        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->assetPairRepository->expects($this->never())->method('findAllByAssetId');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairListProcess->handle($requestDto, $params);
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
        $this->expectException(AssetPairListException::class);

        $this->assetPairRepository->expects($this->never())->method('findAllByAssetId');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairListProcess->handle($requestDto, $params);
    }

    public function testHandleWithAssetPairReadErrorShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $this->assetPairRepository->expects($this->once())
            ->method('findAllByAssetId')
            ->with($assetId)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(AssetPairListException::class);

        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairListProcess->handle($requestDto, $params);
    }

    /**
     * @throws AssetPairListException
     */
    public function testHandleShouldBeOK(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($asset);

        $assetPair = $this->createMock(AssetPair::class);
        $assetPairs = [$assetPair];
        $this->assetPairRepository->expects($this->once())
            ->method('findAllByAssetId')
            ->with($assetId)
            ->willReturn($assetPairs);

        $assetResponseDto = $this->createMock(AssetResponseDto::class);
        $this->assetPairMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($assetPair)
            ->willReturn($assetResponseDto);

        $this->logger->expects($this->never())->method('error');

        /** @var ListResponseDto $response */
        $response = $this->assetPairListProcess->handle($requestDto, $params);

        $this->assertInstanceOf(ListResponseDto::class, $response);
        $this->assertEquals([$assetResponseDto], $response->getData());
    }
}
