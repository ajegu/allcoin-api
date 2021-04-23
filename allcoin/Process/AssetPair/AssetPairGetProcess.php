<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Process\ProcessInterface;

class AssetPairGetProcess extends AbstractAssetPairProcess implements ProcessInterface
{
    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws ItemReadException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $this->assetRepository->findOneById(
            $this->getAssetId($params)
        );

        $assetPair = $this->assetPairRepository->findOneById(
            $this->getAssetPairId($params)
        );

        return $this->assetPairMapper->mapModelToResponseDto($assetPair);
    }

}
