<?php


namespace Test\AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\Asset;
use AllCoin\Process\Asset\AssetListProcess;
use AllCoin\Repository\AssetRepositoryInterface;
use Test\TestCase;

class AssetListProcessTest extends TestCase
{
    private AssetListProcess $assetListProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetMapper $assetMapper;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetMapper = $this->createMock(AssetMapper::class);

        $this->assetListProcess = new AssetListProcess(
            $this->assetRepository,
            $this->assetMapper
        );
    }

    /**
     * @throws ItemReadException
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

        $this->assetMapper->expects($this->once())
            ->method('mapModelToResponseDto')
            ->with($asset)
            ->willReturn($this->createMock(ResponseDtoInterface::class));

        $this->assetListProcess->handle();
    }
}
