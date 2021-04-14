<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
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

        try {
            $assetPair = $this->assetPairRepository->findOneById($assetPairId);
        } catch (ItemReadException $exception) {
            $message = 'The asset pair cannot be found!';
            $this->logger->error($message, [
                'id' => $assetPairId,
                'exception' => $exception->getMessage()
            ]);
            throw new AssetPairGetException($message);
        }

        return $this->assetPairMapper->mapModelToResponseDto($assetPair);
    }

}
