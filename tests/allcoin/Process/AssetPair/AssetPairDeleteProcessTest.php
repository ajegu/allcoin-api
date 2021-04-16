<?php


namespace Test\AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Exception\AssetPair\AssetPairDeleteException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Process\AssetPair\AssetPairDeleteProcess;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetPairDeleteProcessTest extends TestCase
{
    private AssetPairDeleteProcess $assetPairDeleteProcess;

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

        $this->assetPairDeleteProcess = new AssetPairDeleteProcess(
            $this->assetRepository,
            $this->assetPairRepository,
            $this->logger,
            $this->assetPairMapper,
        );
    }

    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $params = [];

        $this->expectException(AssetPairDeleteException::class);

        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->assetPairRepository->expects($this->never())->method('findOneById');
        $this->assetPairRepository->expects($this->never())->method('delete');

        $this->assetPairDeleteProcess->handle(null, $params);
    }

    public function testHandleWithAssetReadErrorShouldThrowException(): void
    {
        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(AssetPairDeleteException::class);

        $this->assetPairRepository->expects($this->never())->method('findOneById');
        $this->assetPairRepository->expects($this->never())->method('delete');

        $this->assetPairDeleteProcess->handle(null, $params);
    }

    public function testHandleWithNoAssetPairIdShouldThrowException(): void
    {
        $assetId = 'foo';
        $params = ['assetId' => $assetId];

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($assetId)
            ->willReturn($this->createMock(Asset::class));

        $this->expectException(AssetPairDeleteException::class);

        $this->logger->expects($this->never())->method('error');
        $this->assetPairRepository->expects($this->never())->method('findOneById');
        $this->assetPairRepository->expects($this->never())->method('delete');

        $this->assetPairDeleteProcess->handle(null, $params);
    }

    public function testHandleWithAssetPairReadErrorShouldThrowException(): void
    {
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
        $this->expectException(AssetPairDeleteException::class);

        $this->assetPairRepository->expects($this->never())->method('delete');

        $this->assetPairDeleteProcess->handle(null, $params);
    }

    public function testHandleWithDeleteErrorShouldThrowException(): void
    {
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
            ->willReturn($this->createMock(AssetPair::class));

        $this->assetPairRepository->expects($this->once())
            ->method('delete')
            ->with($id)
            ->willThrowException($this->createMock(ItemDeleteException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(AssetPairDeleteException::class);

        $this->assetPairDeleteProcess->handle(null, $params);
    }

    /**
     * @throws AssetPairDeleteException
     */
    public function testHandleShouldBeOK(): void
    {
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
            ->willReturn($this->createMock(AssetPair::class));

        $this->assetPairRepository->expects($this->once())
            ->method('delete')
            ->with($id);

        $this->logger->expects($this->never())->method('error');

        $this->assetPairDeleteProcess->handle(null, $params);
    }
}
