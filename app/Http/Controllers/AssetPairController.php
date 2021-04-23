<?php


namespace App\Http\Controllers;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Process\AssetPair\AssetPairCreateProcess;
use AllCoin\Process\AssetPair\AssetPairDeleteProcess;
use AllCoin\Process\AssetPair\AssetPairGetProcess;
use AllCoin\Process\AssetPair\AssetPairListProcess;
use AllCoin\Process\AssetPair\AssetPairUpdateProcess;
use AllCoin\Service\SerializerService;
use AllCoin\Validation\AssetPairValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class AssetPairController extends Controller
{
    public function __construct(
        private AssetPairValidation $assetPairValidation,
        private SerializerService $serializerService,
        private AssetPairCreateProcess $assetPairCreateProcess,
        private AssetPairGetProcess $assetPairGetProcess,
        private AssetPairUpdateProcess $assetPairUpdateProcess,
        private AssetPairListProcess $assetPairListProcess,
        private AssetPairDeleteProcess $assetPairDeleteProcess
    )
    {
    }

    /**
     * @param Request $request
     * @param string $assetId
     * @return JsonResponse
     * @throws ValidationException
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function create(Request $request, string $assetId): JsonResponse
    {
        $payload = $this->validate(
            $request,
            $this->assetPairValidation->getPostRules()
        );

        $requestDto = $this->serializerService->deserializeToRequest(
            $payload,
            AssetPairRequestDto::class
        );

        $responseDto = $this->assetPairCreateProcess->handle(
            $requestDto,
            ['assetId' => $assetId]
        );

        return new JsonResponse(
            $this->serializerService->normalizeResponseDto($responseDto),
            Response::HTTP_CREATED
        );
    }

    /**
     * @param string $assetId
     * @param string $id
     * @return JsonResponse
     * @throws ItemReadException
     */
    public function get(string $assetId, string $id): JsonResponse
    {
        $responseDto = $this->assetPairGetProcess->handle(
            null,
            [
                'assetId' => $assetId,
                'id' => $id
            ]
        );

        return new JsonResponse(
            $this->serializerService->normalizeResponseDto($responseDto),
            Response::HTTP_OK
        );
    }

    /**
     * @param Request $request
     * @param string $assetId
     * @param string $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function update(Request $request, string $assetId, string $id): JsonResponse
    {
        $payload = $this->validate(
            $request,
            $this->assetPairValidation->getPutRules()
        );

        $requestDto = $this->serializerService->deserializeToRequest(
            $payload,
            AssetPairRequestDto::class
        );

        $responseDto = $this->assetPairUpdateProcess->handle(
            $requestDto,
            ['assetId' => $assetId, 'id' => $id]
        );

        return new JsonResponse(
            $this->serializerService->normalizeResponseDto($responseDto),
            Response::HTTP_OK
        );
    }

    /**
     * @param string $assetId
     * @return JsonResponse
     * @throws ItemReadException
     */
    public function list(string $assetId): JsonResponse
    {
        $responseDto = $this->assetPairListProcess->handle(
            null,
            ['assetId' => $assetId]
        );

        return new JsonResponse(
            $this->serializerService->normalizeResponseDto($responseDto),
            Response::HTTP_OK
        );
    }

    /**
     * @param string $assetId
     * @param string $id
     * @return JsonResponse
     * @throws ItemDeleteException
     * @throws ItemReadException
     */
    public function delete(string $assetId, string $id): JsonResponse
    {
        $this->assetPairDeleteProcess->handle(
            null,
            ['assetId' => $assetId, 'id' => $id]
        );

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
