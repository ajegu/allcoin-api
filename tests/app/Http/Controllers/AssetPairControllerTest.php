<?php


namespace Test\App\Http\Controllers;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Dto\AssetPairResponseDto;
use AllCoin\Dto\ListResponseDto;
use AllCoin\Process\AssetPair\AssetPairCreateProcess;
use AllCoin\Process\AssetPair\AssetPairDeleteProcess;
use AllCoin\Process\AssetPair\AssetPairGetProcess;
use AllCoin\Process\AssetPair\AssetPairListProcess;
use AllCoin\Process\AssetPair\AssetPairUpdateProcess;
use AllCoin\Service\SerializerService;
use AllCoin\Validation\AssetPairValidation;
use App\Http\Controllers\AssetPairController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Test\TestCase;

class AssetPairControllerTest extends TestCase
{
    private AssetPairController $assetPairController;

    private AssetPairValidation $assetPairValidation;
    private SerializerService $serializerService;
    private AssetPairCreateProcess $assetPairCreateProcess;
    private AssetPairGetProcess $assetPairGetProcess;
    private AssetPairUpdateProcess $assetPairUpdateProcess;
    private AssetPairListProcess $assetPairListProcess;
    private AssetPairDeleteProcess $assetPairDeleteProcess;

    public function setUp(): void
    {
        $this->assetPairValidation = $this->createMock(AssetPairValidation::class);
        $this->serializerService = $this->createMock(SerializerService::class);
        $this->assetPairCreateProcess = $this->createMock(AssetPairCreateProcess::class);
        $this->assetPairGetProcess = $this->createMock(AssetPairGetProcess::class);
        $this->assetPairUpdateProcess = $this->createMock(AssetPairUpdateProcess::class);
        $this->assetPairListProcess = $this->createMock(AssetPairListProcess::class);
        $this->assetPairDeleteProcess = $this->createMock(AssetPairDeleteProcess::class);

        $this->assetPairController = new AssetPairController(
            $this->assetPairValidation,
            $this->serializerService,
            $this->assetPairCreateProcess,
            $this->assetPairGetProcess,
            $this->assetPairUpdateProcess,
            $this->assetPairListProcess,
            $this->assetPairDeleteProcess,
        );

        parent::setUp();
    }

    /**
     * @throws ValidationException
     * @throws ItemReadException
     * @throws ItemSaveException
     */
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

    /**
     * @throws ItemReadException
     */
    public function testGetShouldBeOK(): void
    {
        $assetId = 'foo';
        $id = 'bar';

        $responseDto = $this->createMock(AssetPairResponseDto::class);
        $this->assetPairGetProcess->expects($this->once())
            ->method('handle')
            ->with(null, [
                'assetId' => $assetId,
                'id' => $id
            ])
            ->willReturn($responseDto);

        $this->serializerService->expects($this->once())
            ->method('normalizeResponseDto')
            ->with($responseDto)
            ->willReturn([]);

        $response = $this->assetPairController->get($assetId, $id);

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @throws ValidationException
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testUpdateShouldBeOK(): void
    {
        $assetId = 'foo';
        $id = 'bar';
        $request = $this->createMock(Request::class);
        $payload = [];
        $request->expects($this->once())->method('all')->willReturn($payload);
        $request->expects($this->once())->method('only')->willReturn($payload);

        $this->assetPairValidation->expects($this->once())
            ->method('getPutRules')
            ->willReturn([]);

        $requestDto = $this->createMock(AssetPairRequestDto::class);
        $this->serializerService->expects($this->once())
            ->method('deserializeToRequest')
            ->with($payload, AssetPairRequestDto::class)
            ->willReturn($requestDto);

        $responseDto = $this->createMock(AssetPairResponseDto::class);
        $this->assetPairUpdateProcess->expects($this->once())
            ->method('handle')
            ->with($requestDto, ['assetId' => $assetId, 'id' => $id])
            ->willReturn($responseDto);

        $this->serializerService->expects($this->once())
            ->method('normalizeResponseDto')
            ->with($responseDto)
            ->willReturn([]);

        $response = $this->assetPairController->update($request, $assetId, $id);

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @throws ItemReadException
     */
    public function testListShouldBeOK(): void
    {
        $assetId = 'foo';

        $responseDto = $this->createMock(ListResponseDto::class);
        $this->assetPairListProcess->expects($this->once())
            ->method('handle')
            ->with(null, ['assetId' => $assetId])
            ->willReturn($responseDto);

        $this->serializerService->expects($this->once())
            ->method('normalizeResponseDto')
            ->with($responseDto);

        $response = $this->assetPairController->list($assetId);

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @throws ItemDeleteException
     * @throws ItemReadException
     */
    public function testDeleteShouldBeOK(): void
    {
        $assetId = 'foo';
        $id = 'bar';

        $this->assetPairDeleteProcess->expects($this->once())
            ->method('handle')
            ->with(
                null,
                ['assetId' => $assetId, 'id' => $id]
            );

        $response = $this->assetPairController->delete($assetId, $id);

        $this->assertJson($response->getContent());
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
