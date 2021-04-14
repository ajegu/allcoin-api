<?php


namespace Test\AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\AssetResponseDto;
use AllCoin\Exception\Asset\AssetGetException;
use AllCoin\Model\Asset;
use AllCoin\Process\Asset\AssetGetProcess;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetGetProcessTest extends TestCase
{
    private AssetGetProcess $assetGetProcess;

    private AssetRepositoryInterface $assetRepository;
    private LoggerInterface $logger;
    private AssetMapper $assetMapper;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->assetMapper = $this->createMock(AssetMapper::class);

        $this->assetGetProcess = new AssetGetProcess(
            $this->assetRepository,
            $this->logger,
            $this->assetMapper,
        );
    }

    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $params = [];

        $this->expectException(AssetGetException::class);

        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->assetMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetGetProcess->handle(null, $params);
    }

    public function testHandleWithReadErrorShouldThrowException(): void
    {
        $id = 'foo';
        $params = ['id' => $id];

        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(AssetGetException::class);

        $this->assetMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetGetProcess->handle(null, $params);
    }

    /**
     * @throws AssetGetException
     */
    public function testHandleShouldBeOK(): void
    {
        $id = 'foo';
        $params = ['id' => $id];

        $asset = $this->createMock(Asset::class);
        $this->assetRepository->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willReturn($asset);


        $responseDto = $this->createMock(AssetResponseDto::class);
        $this->assetMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($asset)
            ->willReturn($responseDto);

        $this->logger->expects($this->never())->method('error');

        $this->assetGetProcess->handle(null, $params);
    }
}
