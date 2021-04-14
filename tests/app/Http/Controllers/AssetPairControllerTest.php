<?php


namespace Test\App\Http\Controllers;


use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Dto\AssetPairResponseDto;
use AllCoin\Process\AssetPair\AssetPairCreateProcess;
use AllCoin\Service\SerializerService;
use AllCoin\Validation\AssetPairValidation;
use App\Http\Controllers\AssetPairController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Test\TestCase;

class AssetPairControllerTest extends TestCase
{
    private AssetPairController $assetPairController;

    private AssetPairValidation $assetPairValidation;
    private SerializerService $serializerService;
    private AssetPairCreateProcess $assetPairCreateProcess;

    public function setUp(): void
    {
        $this->assetPairValidation = $this->createMock(AssetPairValidation::class);
        $this->serializerService = $this->createMock(SerializerService::class);
        $this->assetPairCreateProcess = $this->createMock(AssetPairCreateProcess::class);

        $this->assetPairController = new AssetPairController(
            $this->assetPairValidation,
            $this->serializerService,
            $this->assetPairCreateProcess
        );

        parent::setUp();
    }

    public function testCreateShouldBeOK(): void
    {
        $assetId = 'foo';
        $request = $this->createMock(Request::class);
        $payload = [];
        $request->expects($this->once())->method('all')->willReturn($payload);
        $request->expects($this->once())->method('only')->willReturn($payload);

        $this->assetPairValidation->expects($this->once())
            ->method('getPostRules')
            ->willReturn([]);

        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $this->serializerService->expects($this->once())
            ->method('deserializeToRequest')
            ->with($payload, AssetPairRequestDto::class)
            ->willReturn($requestDto);

        $responseDto = $this->createMock(AssetPairResponseDto::class);
        $this->assetPairCreateProcess->expects($this->once())
            ->method('handle')
            ->with($requestDto, ['assetId' => $assetId])
            ->willReturn($responseDto);

        $this->serializerService->expects($this->once())
            ->method('normalizeResponseDto')
            ->with($responseDto)
            ->willReturn([]);

        $response = $this->assetPairController->create($request, $assetId);

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
