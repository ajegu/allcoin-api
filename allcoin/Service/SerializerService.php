<?php


namespace AllCoin\Service;


use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\SerializerException;
use AllCoin\Model\ModelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerService
{
    const DEFAULT_FORMAT = 'json';

    /**
     * SerializerService constructor.
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $normalizer
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        private SerializerInterface $serializer,
        private NormalizerInterface $normalizer,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @param array $payload
     * @param string $className
     * @return \AllCoin\Dto\RequestDtoInterface
     */
    public function deserializeToRequest(array $payload, string $className): RequestDtoInterface
    {
        try {
            return $this->serializer->deserialize(json_encode($payload), $className, self::DEFAULT_FORMAT);
        } catch (RuntimeException $exception) {
            $this->logger->error('Cannot deserialize payload to request', [
                'payload' => $payload,
                'exception' => $exception->getMessage()
            ]);
            throw new SerializerException();
        }
    }

    /**
     * @param array $payload
     * @param string $className
     * @return \AllCoin\Dto\ResponseDtoInterface
     */
    public function deserializeToResponse(array $payload, string $className): ResponseDtoInterface
    {
        try {
            return $this->serializer->deserialize(json_encode($payload), $className, self::DEFAULT_FORMAT);
        } catch (RuntimeException $exception) {
            $this->logger->error('Cannot deserialize payload to response', [
                'payload' => $payload,
                'exception' => $exception->getMessage()
            ]);
            throw new SerializerException();
        }
    }

    /**
     * @param array $payload
     * @param string $className
     * @return \AllCoin\Model\ModelInterface
     */
    public function deserializeToModel(array $payload, string $className): ModelInterface
    {
        try {
            return $this->serializer->deserialize(json_encode($payload), $className, self::DEFAULT_FORMAT);
        } catch (RuntimeException $exception) {
            $this->logger->error('Cannot deserialize payload to model', [
                'payload' => $payload,
                'exception' => $exception->getMessage()
            ]);
            throw new SerializerException();
        }
    }

    /**
     * @param \AllCoin\Dto\ResponseDtoInterface $responseDto
     * @return array
     */
    public function normalizeResponseDto(ResponseDtoInterface $responseDto): array
    {
        try {
            return $this->normalizer->normalize($responseDto);
        } catch (RuntimeException | ExceptionInterface $exception) {
            $this->logger->error('Cannot normalize response DTO', [
                'class' => get_class($responseDto),
                'exception' => $exception->getMessage()
            ]);
            throw new SerializerException();
        }
    }

    /**
     * @param \AllCoin\Model\ModelInterface $object
     * @return array
     */
    public function normalizeModel(ModelInterface $object): array
    {
        try {
            return $this->normalizer->normalize($object);
        } catch (RuntimeException | ExceptionInterface $exception) {
            $this->logger->error('Cannot normalize model', [
                'class' => get_class($object),
                'exception' => $exception->getMessage()
            ]);
            throw new SerializerException();
        }
    }
}
