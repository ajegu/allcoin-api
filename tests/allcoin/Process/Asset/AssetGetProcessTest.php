<?php


namespace Test\AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\AssetResponseDto;
use AllCoin\Exception\RequiredParameterException;
use AllCoin\Model\Asset;
use AllCoin\Process\Asset\AssetGetProcess;
use AllCoin\Repository\AssetRepositoryInterface;
use Test\TestCase;

class AssetGetProcessTest extends TestCase
{
    private AssetGetProcess $assetGetProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetMapper $assetMapper;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetMapper = $this->createMock(AssetMapper::class);

        $this->assetGetProcess = new AssetGetProcess(
            $this->assetRepository,
            $this->assetMapper,
        );
    }

    /**
     * @throws ItemReadException
     */
    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $params = [];

        $this->expectException(RequiredParameterException::class);

        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->assetMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetGetProcess->handle(null, $params);
    }

    /**
     * @throws ItemReadException
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

        $this->assetGetProcess->handle(null, $params);
    }
}
