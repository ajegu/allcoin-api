<?php


namespace Test\AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Exception\RequiredParameterException;
use AllCoin\Process\Asset\AssetDeleteProcess;
use AllCoin\Repository\AssetRepositoryInterface;
use Test\TestCase;

class AssetDeleteProcessTest extends TestCase
{
    private AssetDeleteProcess $assetDeleteProcess;

    private AssetRepositoryInterface $assetRepository;

    public function setUp(): void
    {
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetMapper = $this->createMock(AssetMapper::class);

        $this->assetDeleteProcess = new AssetDeleteProcess(
            $this->assetRepository,
            $this->assetMapper,
        );
    }

    /**
     * @throws ItemDeleteException
     */
    public function testHandleWithNoAssetIdShouldThrowException(): void
    {
        $params = [];

        $this->expectException(RequiredParameterException::class);

        $this->assetRepository->expects($this->never())->method('delete');

        $this->assetDeleteProcess->handle(null, $params);
    }

    /**
     * @throws ItemDeleteException
     */
    public function testHandleShouldBeOK(): void
    {
        $assetId = 'foo';
        $params = ['id' => $assetId];

        $this->assetRepository->expects($this->once())
            ->method('delete')
            ->with($assetId);

        $this->assetDeleteProcess->handle(null, $params);
    }
}
