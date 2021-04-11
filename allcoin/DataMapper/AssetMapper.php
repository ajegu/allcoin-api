<?php


namespace AllCoin\DataMapper;


use AllCoin\Dto\AssetResponseDto;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\ModelInterface;

class AssetMapper extends AbstractDataMapper implements DataMapperInterface
{
    public function mapModelToResponseDto(ModelInterface $model): ResponseDtoInterface
    {
        return $this->convertModelToResponseDto($model, AssetResponseDto::class);
    }

}
