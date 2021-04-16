<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\AssetPair\AssetPairDeleteException;
use AllCoin\Process\ProcessInterface;

class AssetPairDeleteProcess extends AbstractAssetPairProcess implements ProcessInterface
{
    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws AssetPairDeleteException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assetId = $this->getAssetId($params, AssetPairDeleteException::class);
        $this->getAsset($assetId, AssetPairDeleteException::class);

        $assetPairId = $this->getAssetPairId($params, AssetPairDeleteException::class);
        $this->getAssetPair($assetPairId, AssetPairDeleteException::class);

        try {
            $this->assetPairRepository->delete($assetPairId);
        } catch (ItemDeleteException $exception) {
            $message = 'The asset pair cannot be deleted!';
            $this->logger->error($message, [
                'assetId' => $assetId,
                'assetPairId' => $assetPairId,
                'exception' => $exception->getMessage()
            ]);

            throw new AssetPairDeleteException($message);
        }

        return null;
    }

}
