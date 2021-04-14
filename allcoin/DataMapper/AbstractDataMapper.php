<?php


namespace AllCoin\DataMapper;


use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\ModelInterface;
use AllCoin\Service\SerializerService;

abstract class AbstractDataMapper
{

    /**
     * AbstractDataMapper constructor.
     * @param SerializerService $serializerService
     */
    public function __construct(
        protected SerializerService $serializerService
    )
    {
    }

    /**
     * @param ModelInterface $model
     * @param string $responseDtoClass
     * @return ResponseDtoInterface
     */
    public function convertModelToResponseDto(ModelInterface $model, string $responseDtoClass): ResponseDtoInterface
    {
        $data = $this->serializerService->normalizeModel($model);
        return $this->serializerService->deserializeToResponse($data, $responseDtoClass);
    }
}
