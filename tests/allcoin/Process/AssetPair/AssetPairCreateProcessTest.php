<?php


namespace Test\AllCoin\Process\AssetPair;


use AllCoin\Builder\AssetPairBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Dto\AssetPairResponseDto;
use AllCoin\Exception\RequiredParameterException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Process\AssetPair\AssetPairCreateProcess;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Test\TestCase;

class AssetPairCreateProcessTest extends TestCase
{
    private AssetPairCreateProcess $assetPairCreateProcess;

    private AssetRepositoryInterface $assetRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private AssetPairMapper $assetPairMapper;
    private AssetPairBuilder $assetPairBuilder;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->assetPairMapper = $this->createMock(AssetPairMapper::class);
        $this->assetPairBuilder = $this->createMock(AssetPairBuilder::class);

        $this->assetPairCreateProcess = new AssetPairCreateProcess(
            $this->assetRepository,
            $this->assetPairRepository,
            $this->assetPairMapper,
            $this->assetPairBuilder,
        );
    }

    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $params = [];

        $this->expectException(RequiredParameterException::class);

        $this->assetRepository->expects($this->never())->method('findOneById');
        $this->assetPairBuilder->expects($this->never())->method('build');
        $this->assetPairRepository->expects($this->never())->method('save');
        $this->assetPairMapper->expects($this->never())->method('mapModelToResponseDto');

        $this->assetPairCreateProcess->handle($requestDto, $params);
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
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

        $this->assetPairCreateProcess->handle($requestDto, $params);
    }
}
