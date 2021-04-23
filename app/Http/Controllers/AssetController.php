<?php


namespace App\Http\Controllers;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Dto\AssetRequestDto;
use AllCoin\Process\Asset\AssetCreateProcess;
use AllCoin\Process\Asset\AssetDeleteProcess;
use AllCoin\Process\Asset\AssetGetProcess;
use AllCoin\Process\Asset\AssetListProcess;
use AllCoin\Process\Asset\AssetUpdateProcess;
use AllCoin\Service\SerializerService;
use AllCoin\Validation\AssetValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

final class AssetController extends Controller
{
    public function __construct(
        private SerializerService $serializerService,
        private AssetValidation $assetValidation,
        private AssetCreateProcess $assetCreateProcess,
        private AssetListProcess $assetListProcess,
        private AssetUpdateProcess $assetUpdateProcess,
        private AssetDeleteProcess $assetDeleteProcess,
        private AssetGetProcess $assetGetProcess
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws ItemSaveException
     */
    public function create(Request $request): JsonResponse
    {
        $payload = $this->validate($request, $this->assetValidation->getPostRules());

        $requestDto = $this->serializerService->deserializeToRequest($payload, AssetRequestDto::class);
        $responseDto = $this->assetCreateProcess->handle($requestDto);

        return new JsonResponse(
            $this->serializerService->normalizeResponseDto($responseDto),
            Response::HTTP_CREATED
        );
    }

    /**
     * @return JsonResponse
     * @throws ItemReadException
     */
    public function list(): JsonResponse
    {
        return new JsonResponse(
            $this->serializerService->normalizeResponseDto(
                $this->assetListProcess->handle()
            )
        );
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     * @throws ItemReadException
     * @throws ItemSaveException
     * @throws ValidationException
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $payload = $this->validate($request, $this->assetValidation->getPutRules());

        $requestDto = $this->serializerService->deserializeToRequest($payload, AssetRequestDto::class);
        $responseDto = $this->assetUpdateProcess->handle($requestDto, ['id' => $id]);

        return new JsonResponse(
            $this->serializerService->normalizeResponseDto($responseDto),
            Response::HTTP_OK
        );
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemDeleteException
     */
    public function delete(string $id): JsonResponse
    {
        $this->assetDeleteProcess->handle(null, ['id' => $id]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $id
     * @return JsonResponse
     * @throws ItemReadException
     */
    public function get(string $id): JsonResponse
    {
        $responseDto = $this->assetGetProcess->handle(null, ['id' => $id]);

        return new JsonResponse(
            $this->serializerService->normalizeResponseDto($responseDto),
            Response::HTTP_OK
        );
    }
}
