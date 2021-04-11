<?php


namespace AllCoin\DataMapper;


use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\ModelInterface;
use AllCoin\Service\SerializerService;

abstract class AbstractDataMapper
{

    /**
     * AbstractDataMapper constructor.
     * @param \AllCoin\Service\SerializerService $serializerService
     */
    public function __construct(
        protected SerializerService $serializerService
    )
    {
    }

    /**
     * @param \AllCoin\Model\ModelInterface $model
     * @return array
     */
    public function convertModelToArray(ModelInterface $model): array
    {
        return $this->serializerService->normalizeModel($model);
    }

    /**
     * @param \AllCoin\Model\ModelInterface $model
     * @param string $responseDtoClass
     * @return \AllCoin\Dto\ResponseDtoInterface
     */
    public function convertModelToResponseDto(ModelInterface $model, string $responseDtoClass): ResponseDtoInterface
    {
        $data = $this->serializerService->normalizeModel($model);
        return $this->serializerService->deserializeToResponse($data, $responseDtoClass);
    }
}
