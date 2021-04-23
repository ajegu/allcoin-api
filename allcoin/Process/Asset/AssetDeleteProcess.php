<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Process\ProcessInterface;

class AssetDeleteProcess extends AbstractAssetProcess implements ProcessInterface
{
    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws ItemDeleteException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $this->assetRepository->delete(
            $this->getAssetId($params)
        );

        return null;
    }

}
