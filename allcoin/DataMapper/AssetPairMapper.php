<?php


namespace AllCoin\DataMapper;


use AllCoin\Dto\AssetPairResponseDto;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\ModelInterface;

class AssetPairMapper extends AbstractDataMapper implements DataMapperInterface
{
    public function mapModelToResponseDto(ModelInterface $model): ResponseDtoInterface
    {
        return $this->convertModelToResponseDto($model, AssetPairResponseDto::class);
    }

}
