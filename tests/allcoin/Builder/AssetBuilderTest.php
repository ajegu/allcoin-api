<?php


namespace Test\AllCoin\Builder;


use AllCoin\Builder\AssetBuilder;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\UuidService;
use DateTime;
use Test\TestCase;

class AssetBuilderTest extends TestCase
{
    private AssetBuilder $assetBuilder;

    private UuidService $uuidService;
    private DateTimeService $dateTimeService;

    public function setUp(): void
    {
        $this->uuidService = $this->createMock(UuidService::class);
        $this->dateTimeService = $this->createMock(DateTimeService::class);

        $this->assetBuilder = new AssetBuilder(
            $this->uuidService,
            $this->dateTimeService
        );
    }

    public function testBuildShouldBeOK(): void
    {
        $name = 'foo';

        $uuid = 'bar';
        $this->uuidService->expects($this->once())
            ->method('generateUuid')
            ->willReturn($uuid);

        $createdAt = new DateTime();
        $this->dateTimeService->expects($this->once())
            ->method('now')
            ->willReturn($createdAt);

        $asset = $this->assetBuilder->build($name);

        $this->assertEquals($uuid, $asset->getId());
        $this->assertEquals($name, $asset->getName());
        $this->assertEquals($createdAt, $asset->getCreatedAt());
        $this->assertNull($asset->getUpdatedAt());
    }
}
