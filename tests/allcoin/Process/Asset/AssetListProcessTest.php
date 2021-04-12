<?php


namespace Test\AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ReadException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\Asset\AssetListException;
use AllCoin\Model\Asset;
use AllCoin\Process\Asset\AssetListProcess;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetListProcessTest extends TestCase
{
    private AssetListProcess $assetListProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetMapper $assetMapper;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetMapper = $this->createMock(AssetMapper::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->assetListProcess = new AssetListProcess(
            $this->assetRepository,
            $this->assetMapper,
            $this->logger
        );
    }

    public function testHandleWithReadErrorShouldThrowException(): void
    {
        $this->assetRepository->expects($this->once())
            ->method('findAll')
            ->willThrowException($this->createMock(ReadException::class));

        $this->logger->expects($this->once())->method('error');

        $this->assetMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->expectException(AssetListException::class);

        $this->assetListProcess->handle();
    }

    /**
     * @throws \AllCoin\Exception\Asset\AssetListException
     */
    public function testHandleShouldBeOK(): void
    {
        $asset = $this->createMock(Asset::class);

        $assets = [
            $asset
        ];
        $this->assetRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($assets);

        $this->logger->expects($this->never())->method('error');

        $this->assetMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($asset)
            ->willReturn($this->createMock(ResponseDtoInterface::class));

        $this->assetListProcess->handle();
    }
}
