<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Dto\ListResponseDto;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\AssetPair\AssetPairListException;
use AllCoin\Model\AssetPair;
use AllCoin\Process\ProcessInterface;

class AssetPairListProcess extends AbstractAssetPairProcess implements ProcessInterface
{
    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws AssetPairListException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assetId = $this->getAssetId($params, AssetPairListException::class);
        $this->getAsset($assetId, AssetPairListException::class);

        try {
            $assetPairs = $this->assetPairRepository->findAllByAssetId($assetId);
        } catch (ItemReadException $exception) {
            $message = 'The assets pairs cannot be found!';
            $this->logger->error($message, [
                'id' => $assetId,
                'exception' => $exception->getMessage()
            ]);
            throw new AssetPairListException($message);
        }

        $assetPairsDto = array_map(function (AssetPair $assetPair) {
            return $this->assetPairMapper->mapModelToResponseDto($assetPair);
        }, $assetPairs);

        return new ListResponseDto($assetPairsDto);
    }

}
