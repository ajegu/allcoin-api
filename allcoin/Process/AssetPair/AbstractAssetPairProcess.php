<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractAssetPairProcess
{
    public function __construct(
        protected AssetRepositoryInterface $assetRepository,
        protected AssetPairRepositoryInterface $assetPairRepository,
        protected LoggerInterface $logger,
        protected AssetPairMapper $assetPairMapper
    )
    {
    }

    /**
     * @param array $params
     * @param string $exceptionClass
     * @return string
     */
    protected function getAssetId(array $params, string $exceptionClass): string
    {
        $assetId = $params['assetId'] ?? false;
        if (!$assetId) {
            throw new $exceptionClass('The asset ID must be defined in $params');
        }

        return $assetId;
    }

    /**
     * @param string $assetId
     * @param string $exceptionClass
     * @return Asset
     */
    protected function getAsset(string $assetId, string $exceptionClass): Asset
    {
        try {
            return $this->assetRepository->findOneById($assetId);
        } catch (ItemReadException $exception) {
            $message = 'The asset cannot be found!';
            $this->logger->error($message, [
                'id' => $assetId,
                'exception' => $exception->getMessage()
            ]);
            throw new $exceptionClass($message);
        }
    }

    /**
     * @param array $params
     * @param string $exceptionClass
     * @return string
     */
    protected function getAssetPairId(array $params, string $exceptionClass): string
    {
        $assetPairId = $params['id'] ?? false;
        if (!$assetPairId) {
            throw new $exceptionClass('The asset pair ID must be defined in $params');
        }

        return $assetPairId;
    }

    /**
     * @param string $assetPairId
     * @param string $exceptionClass
     * @return AssetPair
     */
    protected function getAssetPair(string $assetPairId, string $exceptionClass): AssetPair
    {
        try {
            return $this->assetPairRepository->findOneById($assetPairId);
        } catch (ItemReadException $exception) {
            $message = 'The asset pair cannot be found!';
            $this->logger->error($message, [
                'id' => $assetPairId,
                'exception' => $exception->getMessage()
            ]);
            throw new $exceptionClass($message);
        }
    }
}
