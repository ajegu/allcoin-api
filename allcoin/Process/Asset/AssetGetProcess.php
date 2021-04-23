<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Process\ProcessInterface;

class AssetGetProcess extends AbstractAssetProcess implements ProcessInterface
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

        return $this->assetMapper->mapModelToResponseDto($asset);
    }

}
