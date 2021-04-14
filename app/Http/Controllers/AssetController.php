<?php


namespace App\Http\Controllers;


use AllCoin\Dto\AssetRequestDto;
use AllCoin\Exception\Asset\AssetCreateException;
use AllCoin\Exception\Asset\AssetDeleteException;
use AllCoin\Exception\Asset\AssetListException;
use AllCoin\Exception\Asset\AssetUpdateException;
use AllCoin\Process\Asset\AssetCreateProcess;
use AllCoin\Process\Asset\AssetDeleteProcess;
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
        private AssetDeleteProcess $assetDeleteProcess
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AssetCreateException
     * @throws ValidationException
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
     * @throws AssetListException
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
     * @throws ValidationException
     * @throws AssetUpdateException
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
     * @throws AssetDeleteException
     */
    public function delete(string $id): JsonResponse
    {
        $this->assetDeleteProcess->handle(null, ['id' => $id]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
