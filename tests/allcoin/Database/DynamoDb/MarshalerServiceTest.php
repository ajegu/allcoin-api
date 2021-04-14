<?php


namespace Test\AllCoin\Database\DynamoDb;


use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\MarshalerService;
use Aws\DynamoDb\Marshaler;
use Psr\Log\LoggerInterface;
use Test\TestCase;
use UnexpectedValueException;

class MarshalerServiceTest extends TestCase
{
    private MarshalerService $marshalerService;

    private Marshaler $marshaler;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->marshaler = $this->createMock(Marshaler::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->marshalerService = new MarshalerService(
            $this->marshaler,
            $this->logger
        );
    }

    public function testMarshalItemWithErrorShouldThrowException(): void
    {
        $item = [];

        $this->marshaler->expects($this->once())
            ->method('marshalItem')
            ->with($item)
            ->willThrowException($this->createMock(UnexpectedValueException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(MarshalerException::class);

        $this->marshalerService->marshalItem($item);
    }

    /**
     * @throws MarshalerException
     */
    public function testMarshalItemShouldBeOK(): void
    {
        $item = [];

        $itemMarshaled = [];
        $this->marshaler->expects($this->once())
            ->method('marshalItem')
            ->with($item)
            ->willReturn($itemMarshaled);

        $this->logger->expects($this->never())
            ->method('error');

        $this->marshalerService->marshalItem($item);
    }

    public function testMarshalValueWithErrorShouldThrowException(): void
    {
        $value = 'foo';

        $this->marshaler->expects($this->once())
            ->method('marshalValue')
            ->with($value)
            ->willThrowException($this->createMock(UnexpectedValueException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(MarshalerException::class);

        $this->marshalerService->marshalValue($value);
    }

    /**
     * @throws MarshalerException
     */
    public function testMarshalValueShouldBeOK(): void
    {
        $value = 'foo';

        $valueMarshaled = ['foo' => 'bar'];
        $this->marshaler->expects($this->once())
            ->method('marshalValue')
            ->with($value)
            ->willReturn($valueMarshaled);

        $this->logger->expects($this->never())
            ->method('error');

        $this->marshalerService->marshalValue($value);
    }

    public function testUnmarshalItemWithErrorShouldThrowException(): void
    {
        $item = [];

        $this->marshaler->expects($this->once())
            ->method('unmarshalItem')
            ->with($item)
            ->willThrowException($this->createMock(UnexpectedValueException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(MarshalerException::class);

        $this->marshalerService->unmarshalItem($item);
    }

    /**
     * @throws MarshalerException
     */
    public function testUnmarshalItemShouldBeOK(): void
    {
        $item = [];

        $itemMarshaled = [];
        $this->marshaler->expects($this->once())
            ->method('unmarshalItem')
            ->with($item)
            ->willReturn($itemMarshaled);

        $this->logger->expects($this->never())
            ->method('error');

        $this->marshalerService->unmarshalItem($item);
    }
}
