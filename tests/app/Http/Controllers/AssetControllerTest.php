<?php


namespace Test\App\Http\Controllers;


use AllCoin\Dto\AssetRequestDto;
use AllCoin\Dto\AssetResponseDto;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Process\Asset\AssetCreateProcess;
use AllCoin\Process\Asset\AssetListProcess;
use AllCoin\Process\Asset\AssetUpdateProcess;
use AllCoin\Service\SerializerService;
use AllCoin\Validation\AssetValidation;
use App\Http\Controllers\AssetController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Test\TestCase;

class AssetControllerTest extends TestCase
{
    private AssetController $assetController;
    private AssetValidation $assetValidation;
    private AssetCreateProcess $assetCreateProcess;
    private SerializerService $serializerService;
    private AssetListProcess $assetListProcess;
    private AssetUpdateProcess $assetUpdateProcess;

    public function setUp(): void
    {
        $this->assetValidation = $this->createMock(AssetValidation::class);
        $this->assetCreateProcess = $this->createMock(AssetCreateProcess::class);
        $this->serializerService = $this->createMock(SerializerService::class);
        $this->assetListProcess = $this->createMock(AssetListProcess::class);
        $this->assetUpdateProcess = $this->createMock(AssetUpdateProcess::class);

        $this->assetController = new AssetController(
            $this->serializerService,
            $this->assetValidation,
            $this->assetCreateProcess,
            $this->assetListProcess,
            $this->assetUpdateProcess,
        );

        parent::setUp();
    }

    /**
     * @throws \AllCoin\Exception\Asset\AssetCreateException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function testCreateShouldBeOK(): void
    {
        $request = $this->createMock(Request::class);
        $payload = [];
        $request->expects($this->once())->method('all')->willReturn($payload);
        $request->expects($this->once())->method('only')->willReturn($payload);

        $this->assetValidation->expects($this->once())
            ->method('getPostRules')
            ->willReturn([]);

        $requestDto = $this->createMock(AssetRequestDto::class);
        $this->serializerService->expects($this->once())
            ->method('deserializeToRequest')
            ->with($payload, AssetRequestDto::class)
            ->willReturn($requestDto);

        $responseDto = $this->createMock(AssetResponseDto::class);
        $this->assetCreateProcess->expects($this->once())
            ->method('handle')
            ->with($requestDto)
            ->willReturn($responseDto);

        $this->serializerService->expects($this->once())
            ->method('normalizeResponseDto')
            ->with($responseDto)
            ->willReturn([]);

        $response = $this->assetController->create($request);

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testListShouldBeOK(): void
    {
        $responseDto = $this->createMock(ResponseDtoInterface::class);
        $this->assetListProcess->expects($this->once())
            ->method('handle')
            ->willReturn($responseDto);

        $this->serializerService->expects($this->once())
            ->method('normalizeResponseDto')
            ->willReturn([]);

        $response = $this->assetController->list();

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @throws \AllCoin\Exception\Asset\AssetUpdateException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function testUpdateShouldBeOK(): void
    {
        $assetId = 'foo';
        $request = $this->createMock(Request::class);
        $payload = [];
        $request->expects($this->once())->method('all')->willReturn($payload);
        $request->expects($this->once())->method('only')->willReturn($payload);

        $this->assetValidation->expects($this->once())
            ->method('getPutRules')
            ->willReturn([]);

        $requestDto = $this->createMock(AssetRequestDto::class);
        $this->serializerService->expects($this->once())
            ->method('deserializeToRequest')
            ->with($payload, AssetRequestDto::class)
            ->willReturn($requestDto);

        $responseDto = $this->createMock(AssetResponseDto::class);
        $this->assetUpdateProcess->expects($this->once())
            ->method('handle')
            ->with($requestDto, ['id' => $assetId])
            ->willReturn($responseDto);

        $this->serializerService->expects($this->once())
            ->method('normalizeResponseDto')
            ->with($responseDto)
            ->willReturn([]);

        $response = $this->assetController->update($request, $assetId);

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
