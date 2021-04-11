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

    public function testMarshalItemWithUnexpectedValueExceptionShouldThrowException(): void
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
     * @throws \AllCoin\Database\DynamoDb\Exception\MarshalerException
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
}
