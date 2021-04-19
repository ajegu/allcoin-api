<?php


namespace Test\AllCoin\Builder;


use AllCoin\Builder\AssetPairBuilder;
use AllCoin\Model\Asset;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\UuidService;
use DateTime;
use Test\TestCase;

class AssetPairBuilderTest extends TestCase
{
    private AssetPairBuilder $assetPairBuilder;

    private UuidService $uuidService;
    private DateTimeService $dateTimeService;

    public function setUp(): void
    {
        $this->uuidService = $this->createMock(UuidService::class);
        $this->dateTimeService = $this->createMock(DateTimeService::class);

        $this->assetPairBuilder = new AssetPairBuilder(
            $this->uuidService,
            $this->dateTimeService
        );
    }

    public function testBuildShouldBeOK(): void
    {
        $asset = $this->createMock(Asset::class);
        $name = 'foo';

        $uuid = 'bar';
        $this->uuidService->expects($this->once())
            ->method('generateUuid')
            ->willReturn($uuid);

        $createdAt = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($createdAt);

        $assetPair = $this->assetPairBuilder->build($asset, $name);

        $this->assertEquals($uuid, $assetPair->getId());
        $this->assertEquals($asset, $assetPair->getAsset());
        $this->assertEquals($name, $assetPair->getName());
        $this->assertEquals($createdAt, $assetPair->getCreatedAt());
        $this->assertNull($assetPair->getUpdatedAt());
    }
}
