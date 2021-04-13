<?php


namespace App\Http\Controllers;


use AllCoin\Dto\AssetRequestDto;
use AllCoin\Process\Asset\AssetCreateProcess;
use AllCoin\Process\Asset\AssetDeleteProcess;
use AllCoin\Process\Asset\AssetListProcess;
use AllCoin\Process\Asset\AssetUpdateProcess;
use AllCoin\Service\SerializerService;
use AllCoin\Validation\AssetValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \AllCoin\Exception\Asset\AssetCreateException
     * @throws \Illuminate\Validation\ValidationException
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
     * @return \Illuminate\Http\JsonResponse
     * @throws \AllCoin\Exception\Asset\AssetListException
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
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \AllCoin\Exception\Asset\AssetUpdateException
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
     * @return \Illuminate\Http\JsonResponse
     * @throws \AllCoin\Exception\Asset\AssetDeleteException
     */
    public function delete(string $id): JsonResponse
    {
        $this->assetDeleteProcess->handle(null, ['id' => $id]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
