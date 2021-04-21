<?php


namespace Test\AllCoin\Service;


use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\SerializerException;
use AllCoin\Model\EventInterface;
use AllCoin\Model\ModelInterface;
use AllCoin\Service\SerializerService;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Test\TestCase;

class SerializerServiceTest extends TestCase
{
    private SerializerService $serializerService;

    private SerializerInterface $serializer;
    private NormalizerInterface $normalizer;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->serializerService = new SerializerService(
            $this->serializer,
            $this->normalizer,
            $this->logger
        );
    }

    public function testDeserializeToRequestWithSerializerErrorShouldThrowException(): void
    {
        $payload = [];
        $className = 'foo';

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($payload), $className, SerializerService::DEFAULT_FORMAT)
            ->willThrowException($this->createMock(RuntimeException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(SerializerException::class);

        $this->serializerService->deserializeToRequest($payload, $className);
    }

    public function testDeserializeToRequestShouldBeOK(): void
    {
        $payload = [];
        $className = 'foo';

        $dto = $this->createMock(RequestDtoInterface::class);
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($payload), $className, SerializerService::DEFAULT_FORMAT)
            ->willReturn($dto);

        $this->logger->expects($this->never())->method('error');

        $dto = $this->serializerService->deserializeToRequest($payload, $className);

        $this->assertInstanceOf(RequestDtoInterface::class, $dto);
    }

    public function testDeserializeToResponseWithSerializerErrorShouldThrowException(): void
    {
        $payload = [];
        $className = 'foo';

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($payload), $className, SerializerService::DEFAULT_FORMAT)
            ->willThrowException($this->createMock(RuntimeException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(SerializerException::class);

        $this->serializerService->deserializeToResponse($payload, $className);
    }

    public function testDeserializeToResponseShouldBeOK(): void
    {
        $payload = [];
        $className = 'foo';

        $dto = $this->createMock(ResponseDtoInterface::class);
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($payload), $className, SerializerService::DEFAULT_FORMAT)
            ->willReturn($dto);

        $this->logger->expects($this->never())->method('error');

        $dto = $this->serializerService->deserializeToResponse($payload, $className);

        $this->assertInstanceOf(ResponseDtoInterface::class, $dto);
    }

    public function testDeserializeToModelWithSerializerErrorShouldThrowException(): void
    {
        $payload = [];
        $className = 'foo';

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($payload), $className, SerializerService::DEFAULT_FORMAT)
            ->willThrowException($this->createMock(RuntimeException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(SerializerException::class);

        $this->serializerService->deserializeToModel($payload, $className);
    }

    public function testDeserializeToModelShouldBeOK(): void
    {
        $payload = [];
        $className = 'foo';

        $dto = $this->createMock(ModelInterface::class);
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with(json_encode($payload), $className, SerializerService::DEFAULT_FORMAT)
            ->willReturn($dto);

        $this->logger->expects($this->never())->method('error');

        $dto = $this->serializerService->deserializeToModel($payload, $className);

        $this->assertInstanceOf(ModelInterface::class, $dto);
    }

    public function testDeserializeToEventWithSerializerErrorShouldThrowException(): void
    {
        $payload = '';
        $className = 'foo';

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($payload, $className, SerializerService::DEFAULT_FORMAT)
            ->willThrowException($this->createMock(RuntimeException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(SerializerException::class);

        $this->serializerService->deserializeToEvent($payload, $className);
    }

    public function testDeserializeToEventShouldBeOK(): void
    {
        $payload = '';
        $className = 'foo';

        $dto = $this->createMock(EventInterface::class);
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($payload, $className, SerializerService::DEFAULT_FORMAT)
            ->willReturn($dto);

        $this->logger->expects($this->never())->method('error');

        $dto = $this->serializerService->deserializeToEvent($payload, $className);

        $this->assertInstanceOf(EventInterface::class, $dto);
    }

    public function testNormalizeResponseDtoWithNormalizerErrorShouldThrowException(): void
    {
        $responseDto = $this->createMock(ResponseDtoInterface::class);

        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($responseDto)
            ->willThrowException($this->createMock(ExceptionInterface::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(SerializerException::class);

        $this->serializerService->normalizeResponseDto($responseDto);
    }

    public function testNormalizeResponseDtoShouldBeOK(): void
    {
        $responseDto = $this->createMock(ResponseDtoInterface::class);

        $data = [];
        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($responseDto)
            ->willReturn($data);

        $this->logger->expects($this->never())->method('error');

        $this->serializerService->normalizeResponseDto($responseDto);
    }

    public function testNormalizeModelWithNormalizerErrorShouldThrowException(): void
    {
        $responseDto = $this->createMock(ModelInterface::class);

        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($responseDto)
            ->willThrowException($this->createMock(ExceptionInterface::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(SerializerException::class);

        $this->serializerService->normalizeModel($responseDto);
    }

    public function testNormalizeModelShouldBeOK(): void
    {
        $responseDto = $this->createMock(ModelInterface::class);

        $data = [];
        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($responseDto)
            ->willReturn($data);

        $this->logger->expects($this->never())->method('error');

        $this->serializerService->normalizeModel($responseDto);
    }

    public function testSerializeObjectWithErrorShouldThrowException(): void
    {
        $object = new stdClass();

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($object)
            ->willThrowException($this->createMock(RuntimeException::class));

        $this->logger->expects($this->once())->method('error');

        $this->expectException(SerializerException::class);

        $this->serializerService->serializeObject($object);
    }


    public function testSerializeObjectShouldBeOK(): void
    {
        $object = new stdClass();

        $data = '';
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($object)
            ->willReturn($data);

        $this->logger->expects($this->never())->method('error');

        $result = $this->serializerService->serializeObject($object);
        $this->assertEquals($data, $result);
    }
}
