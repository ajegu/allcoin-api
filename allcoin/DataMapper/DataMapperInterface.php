<?php


namespace AllCoin\DataMapper;


use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\ModelInterface;

interface DataMapperInterface
{
    public function mapModelToResponseDto(ModelInterface $model): ResponseDtoInterface;
}
