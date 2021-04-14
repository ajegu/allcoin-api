<?php


namespace Test\AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Exception\Asset\AssetDeleteException;
use AllCoin\Process\Asset\AssetDeleteProcess;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetDeleteProcessTest extends TestCase
{
    private AssetDeleteProcess $assetDeleteProcess;

    private AssetRepositoryInterface $assetRepository;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->assetDeleteProcess = new AssetDeleteProcess(
            $this->assetRepository,
            $this->logger
        );
    }

    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $params = [];

        $this->expectException(AssetDeleteException::class);

        $this->assetRepository->expects($this->never())->method('delete');

        $this->assetDeleteProcess->handle(null, $params);
    }

    public function testHandleWithDeleteErrorShouldThrowException(): void
    {
        $assetId = 'foo';
        $params = ['id' => $assetId];

        $this->assetRepository->expects($this->once())
            ->method('delete')
            ->with($assetId)
            ->willThrowException($this->createMock(ItemDeleteException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(AssetDeleteException::class);

        $this->assetDeleteProcess->handle(null, $params);
    }

    /**
     * @throws AssetDeleteException
     */
    public function testHandleShouldBeOK(): void
    {
        $assetId = 'foo';
        $params = ['id' => $assetId];

        $this->assetRepository->expects($this->once())
            ->method('delete')
            ->with($assetId);

        $this->logger->expects($this->never())->method('error');

        $this->assetDeleteProcess->handle(null, $params);
    }
}
