<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Dto\ListResponseDto;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\AssetPair;
use AllCoin\Process\ProcessInterface;

class AssetPairListProcess extends AbstractAssetPairProcess implements ProcessInterface
{
    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws ItemReadException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $asset = $this->assetRepository->findOneById(
            $this->getAssetId($params)
        );

        $assetPairs = $this->assetPairRepository->findAllByAssetId($asset->getId());

        $assetPairsDto = array_map(function (AssetPair $assetPair) {
            return $this->assetPairMapper->mapModelToResponseDto($assetPair);
        }, $assetPairs);

        return new ListResponseDto($assetPairsDto);
    }

}
