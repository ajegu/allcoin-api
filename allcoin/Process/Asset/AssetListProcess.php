<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Dto\ListResponseDto;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\Asset;
use AllCoin\Process\ProcessInterface;

class AssetListProcess extends AbstractAssetProcess implements ProcessInterface
{
    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface
     * @throws ItemReadException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ResponseDtoInterface
    {
        $assets = $this->assetRepository->findAll();

        $assetsDto = array_map(function (Asset $asset) {
            return $this->assetMapper->mapModelToResponseDto($asset);
        }, $assets);

        return new ListResponseDto($assetsDto);
    }

}
