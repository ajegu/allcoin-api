<?php


namespace Test\AllCoin\Database\DynamoDb\Manager\ItemReadManager;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\MarshalerService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Result;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class fetchAllBetweenTest extends TestCase
{
    private ItemManager $itemManager;

    private DynamoDbClient $dynamoDbClient;
    private MarshalerService $marshalerService;
    private LoggerInterface $logger;
    private string $tableName = 'table';

    public function setUp(): void
    {
        $this->dynamoDbClient = $this->createMock(DynamoDbClient::class);
        $this->marshalerService = $this->createMock(MarshalerService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->itemManager = new ItemManager(
            $this->dynamoDbClient,
            $this->marshalerService,
            $this->logger,
            $this->tableName,
        );
    }

    public function testFetchAllWithMarshalValueErrorShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $start = 'bar';
        $end = 'baz';

        $this->marshalerService->expects($this->once())
            ->method('marshalValue')
            ->with($partitionKey)
            ->willThrowException($this->createMock(MarshalerException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(ItemReadException::class);

        $this->dynamoDbClient->expects($this->never())
            ->method('__call');
        $this->marshalerService->expects($this->never())->method('unmarshalItem');

        $this->itemManager->fetchAllBetween($partitionKey, $start, $end);
    }

    public function testFetchAllWithDynamoDdQueryErrorShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $start = 'bar';
        $end = 'baz';

        $partitionKeyValue = ['foo' => 'bar'];
        $startValue = ['foo' => 'bar'];
        $endValue = ['foo' => 'bar'];
        $this->marshalerService->expects($this->exactly(3))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$start], [$end])
            ->willReturn($partitionKeyValue, $startValue, $endValue);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' =>
                ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . ItemManager::SORT_KEY_NAME . ' BETWEEN :startValue AND :endValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':startValue' => $startValue,
                ':endValue' => $endValue,
            ]
        ];

        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('query', [$query])
            ->willThrowException($this->createMock(DynamoDbException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(ItemReadException::class);

        $this->marshalerService->expects($this->never())->method('unmarshalItem');

        $this->itemManager->fetchAllBetween($partitionKey, $start, $end);
    }

    public function testFetchAllWithMarshalItemErrorShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $start = 'bar';
        $end = 'baz';

        $partitionKeyValue = ['foo' => 'bar'];
        $startValue = ['foo' => 'bar'];
        $endValue = ['foo' => 'bar'];
        $this->marshalerService->expects($this->exactly(3))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$start], [$end])
            ->willReturn($partitionKeyValue, $startValue, $endValue);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' =>
                ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . ItemManager::SORT_KEY_NAME . ' BETWEEN :startValue AND :endValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':startValue' => $startValue,
                ':endValue' => $endValue,
            ]
        ];

        $item = ['foo' => ''];
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('get')
            ->with('Items')
            ->willReturn([$item]);
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('query', [$query])
            ->willReturn($result);

        $this->marshalerService->expects($this->once())
            ->method('unmarshalItem')
            ->with($item)
            ->willThrowException($this->createMock(MarshalerException::class));

        $this->expectException(ItemReadException::class);
        $this->logger->expects($this->once())->method('error');

        $this->itemManager->fetchAllBetween($partitionKey, $start, $end);
    }

    /**
     * @throws ItemReadException
     */
    public function testFetchAllShouldBeOK(): void
    {
        $partitionKey = 'foo';
        $start = 'bar';
        $end = 'baz';

        $partitionKeyValue = ['foo' => 'bar'];
        $startValue = ['foo' => 'bar'];
        $endValue = ['foo' => 'bar'];
        $this->marshalerService->expects($this->exactly(3))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$start], [$end])
            ->willReturn($partitionKeyValue, $startValue, $endValue);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' =>
                ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . ItemManager::SORT_KEY_NAME . ' BETWEEN :startValue AND :endValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':startValue' => $startValue,
                ':endValue' => $endValue,
            ]
        ];

        $item = ['foo' => ''];
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('get')
            ->with('Items')
            ->willReturn([$item]);
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('query', [$query])
            ->willReturn($result);

        $this->marshalerService->expects($this->once())
            ->method('unmarshalItem')
            ->with($item)
            ->willReturn($item);

        $this->logger->expects($this->never())->method('error');

        $itemsExpected = [
            ['foo' => null]
        ];
        $items = $this->itemManager->fetchAllBetween($partitionKey, $start, $end);

        $this->assertEquals($itemsExpected, $items);
    }
}
