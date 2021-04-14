<?php


namespace Test\AllCoin\DataMapper;


use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\AssetPairResponseDto;
use AllCoin\Model\ModelInterface;
use AllCoin\Service\SerializerService;
use Test\TestCase;

class AssetPairMapperTest extends TestCase
{
    private AssetPairMapper $assetPairMapper;

    private SerializerService $serializerService;

    public function setUp(): void
    {
        $this->serializerService = $this->createMock(SerializerService::class);

        $this->assetPairMapper = new AssetPairMapper(
            $this->serializerService
        );
    }

    public function testMapModelToResponseDtoShouldBeOK(): void
    {
        $model = $this->createMock(ModelInterface::class);

        $data = [];
        $this->serializerService->expects($this->once())
            ->method('normalizeModel')
            ->with($model)
            ->willReturn($data);

        $this->serializerService->expects($this->once())
            ->method('deserializeToResponse')
            ->with($data, AssetPairResponseDto::class);

        $this->assetPairMapper->mapModelToResponseDto($model);
    }
}
