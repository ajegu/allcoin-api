<?php


namespace App\Http\Controllers;


use AllCoin\Dto\AssetRequestDto;
use AllCoin\Process\Asset\AssetCreateProcess;
use AllCoin\Service\SerializerService;
use AllCoin\Validation\AssetValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class AssetController extends Controller
{
    public function __construct(
        private AssetValidation $assetValidation,
        private AssetCreateProcess $assetCreateProcess,
        private SerializerService $serializerService
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
}
