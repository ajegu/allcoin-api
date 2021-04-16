<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\AssetPair\AssetPairGetException;
use AllCoin\Process\ProcessInterface;

class AssetPairGetProcess extends AbstractAssetPairProcess implements ProcessInterface
{
    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws AssetPairGetException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assetId = $this->getAssetId($params, AssetPairGetException::class);
        $this->getAsset($assetId, AssetPairGetException::class);

        $assetPairId = $this->getAssetPairId($params, AssetPairGetException::class);
        $assetPair = $this->getAssetPair($assetPairId, AssetPairGetException::class);

        return $this->assetPairMapper->mapModelToResponseDto($assetPair);
    }

}
