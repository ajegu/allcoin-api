<?php


namespace App\Http\Controllers;


use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Exception\AssetPair\AssetPairCreateException;
use AllCoin\Process\AssetPair\AssetPairCreateProcess;
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
        private AssetPairCreateProcess $assetPairCreateProcess
    )
    {
    }

    /**
     * @param Request $request
     * @param string $assetId
     * @return JsonResponse
     * @throws AssetPairCreateException
     * @throws ValidationException
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
}
