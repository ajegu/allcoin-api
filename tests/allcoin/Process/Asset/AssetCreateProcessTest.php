<?php


namespace Test\AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\PersistenceException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\AssetRequestDto;
use AllCoin\Exception\Asset\AssetCreateException;
use AllCoin\Model\Asset;
use AllCoin\Process\Asset\AssetCreateProcess;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use DateTime;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetCreateProcessTest extends TestCase
{
    private AssetCreateProcess $assetCreateProcess;

    private AssetRepositoryInterface $assetRepository;
    private LoggerInterface $logger;
    private AssetMapper $assetMapper;
    private DateTimeService $dateTimeService;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->assetMapper = $this->createMock(AssetMapper::class);
        $this->dateTimeService = $this->createMock(DateTimeService::class);

        $this->assetCreateProcess = new AssetCreateProcess(
            $this->assetRepository,
            $this->logger,
            $this->assetMapper,
            $this->dateTimeService,
        );
    }

    public function testHandleWithSaveErrorShouldThrowException(): void
    {
        $dto = $this->createMock(AssetRequestDto::class);
        $dtoName = 'foo';
        $dto->expects($this->once())->method('getName')->willReturn($dtoName);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())->method('now')->willReturn($now);

        $asset = new Asset(
            name: $dtoName,
            createdAt: $now
        );

        $this->assetRepository->expects($this->once())
            ->method('save')
            ->with($asset)
            ->willThrowException($this->createMock(PersistenceException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(AssetCreateException::class);

        $this->assetCreateProcess->handle($dto);
    }

    /**
     * @throws \AllCoin\Exception\Asset\AssetCreateException
     */
    public function testHandleShouldBeOK(): void
    {
        $dto = $this->createMock(AssetRequestDto::class);
        $dtoName = 'foo';
        $dto->expects($this->once())->method('getName')->willReturn($dtoName);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())->method('now')->willReturn($now);

        $asset = new Asset(
            name: $dtoName,
            createdAt: $now
        );

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
