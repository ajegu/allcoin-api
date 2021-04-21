<?php


namespace AllCoin\Service;


use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\SerializerException;
use AllCoin\Model\EventInterface;
use AllCoin\Model\ModelInterface;
use Exception;
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
     * @param SerializerInterface $serializer
     * @param NormalizerInterface $normalizer
     * @param LoggerInterface $logger
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
     * @return RequestDtoInterface
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
     * @return ResponseDtoInterface
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
     * @return ModelInterface
     */
    public function deserializeToModel(array $payload, string $className): ModelInterface
    {
        try {
            return $this->serializer->deserialize(json_encode($payload), $className, self::DEFAULT_FORMAT);
        } catch (Exception $exception) {
            $this->logger->error('Cannot deserialize payload to model', [
                'payload' => $payload,
                'exception' => $exception->getMessage()
            ]);
            throw new SerializerException();
        }
    }

    /**
     * @param string $payload
     * @param string $className
     * @return EventInterface
     */
    public function deserializeToEvent(string $payload, string $className): EventInterface
    {
        try {
            return $this->serializer->deserialize($payload, $className, self::DEFAULT_FORMAT);
        } catch (RuntimeException $exception) {
            $this->logger->error('Cannot deserialize payload to event', [
                'payload' => $payload,
                'exception' => $exception->getMessage()
            ]);
            throw new SerializerException();
        }
    }

    /**
     * @param ResponseDtoInterface $responseDto
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
     * @param ModelInterface $object
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

    /**
     * @param object $object
     * @return string
     */
    public function serializeObject(object $object): string
    {
        try {
            return $this->serializer->serialize($object, 'json');
        } catch (RuntimeException $exception) {
            $this->logger->error('Cannot serialize object', [
                'class' => get_class($object),
                'exception' => $exception->getMessage()
            ]);
            throw new SerializerException();
        }
    }
}
