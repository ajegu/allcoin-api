<?php


namespace Test\AllCoin\Process\Asset;


use AllCoin\Builder\AssetBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\AssetRequestDto;
use AllCoin\Exception\Asset\AssetCreateException;
use AllCoin\Model\Asset;
use AllCoin\Process\Asset\AssetCreateProcess;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetCreateProcessTest extends TestCase
{
    private AssetCreateProcess $assetCreateProcess;

    private AssetRepositoryInterface $assetRepository;
    private LoggerInterface $logger;
    private AssetMapper $assetMapper;
    private AssetBuilder $assetBuilder;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->assetMapper = $this->createMock(AssetMapper::class);
        $this->assetBuilder = $this->createMock(AssetBuilder::class);

        $this->assetCreateProcess = new AssetCreateProcess(
            $this->assetRepository,
            $this->logger,
            $this->assetMapper,
            $this->assetBuilder,
        );
    }

    public function testHandleWithSaveErrorShouldThrowException(): void
    {
        $dto = $this->createMock(AssetRequestDto::class);
        $dtoName = 'foo';
        $dto->expects($this->once())->method('getName')->willReturn($dtoName);

        $asset = $this->createMock(Asset::class);
        $this->assetBuilder->expects($this->once())
            ->method('build')
            ->with($dtoName)
            ->willReturn($asset);

        $this->assetRepository->expects($this->once())
            ->method('save')
            ->with($asset)
            ->willThrowException($this->createMock(ItemSaveException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(AssetCreateException::class);

        $this->assetCreateProcess->handle($dto);
    }

    /**
     * @throws AssetCreateException
     */
    public function testHandleShouldBeOK(): void
    {
        $dto = $this->createMock(AssetRequestDto::class);
        $dtoName = 'foo';
        $dto->expects($this->once())->method('getName')->willReturn($dtoName);

        $asset = $this->createMock(Asset::class);
        $this->assetBuilder->expects($this->once())
            ->method('build')
            ->with($dtoName)
            ->willReturn($asset);

        $this->assetRepository->expects($this->once())
            ->method('save')
            ->with($asset);

        $this->logger->expects($this->never())->method('error');

        $this->assetMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($asset);

        $this->assetCreateProcess->handle($dto);
    }
}
