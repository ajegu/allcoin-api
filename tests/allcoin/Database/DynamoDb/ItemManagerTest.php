<?php


namespace Test\AllCoin\Database\DynamoDb;


use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\Exception\PersistenceException;
use AllCoin\Database\DynamoDb\Exception\ReadException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\MarshalerService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Result;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class ItemManagerTest extends TestCase
{
    private $itemManager;

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

    /**
     * @throws \AllCoin\Database\DynamoDb\Exception\PersistenceException
     */
    public function testSaveWithMarshalerErrorShouldThrowException(): void
    {
        $data = [
            'nullValue' => null
        ];
        $partitionKey = 'foo';
        $sortKey = 'bar';

        $dataExpected = [
            'nullValue' => '',
            ItemManager::PARTITION_KEY_NAME => $partitionKey,
            ItemManager::SORT_KEY_NAME => $sortKey
        ];

        $this->marshalerService->expects($this->once())
            ->method('marshalItem')
            ->with($dataExpected)
            ->willThrowException($this->createMock(MarshalerException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(PersistenceException::class);

        $this->dynamoDbClient->expects($this->never())
            ->method('__call')
            ->with('putItem');

        $this->itemManager->save($data, $partitionKey, $sortKey);
    }

    /**
     * @throws \AllCoin\Database\DynamoDb\Exception\PersistenceException
     */
    public function testSaveWithDynamoDbClientErrorShouldThrowException(): void
    {
        $data = [
            'nullValue' => null
        ];
        $partitionKey = 'foo';
        $sortKey = 'bar';

        $dataExpected = [
            'nullValue' => '',
            ItemManager::PARTITION_KEY_NAME => $partitionKey,
            ItemManager::SORT_KEY_NAME => $sortKey
        ];

        $item = [];
        $this->marshalerService->expects($this->once())
            ->method('marshalItem')
            ->with($dataExpected)
            ->willReturn($item);

        $query = [
            'TableName' => $this->tableName,
            'Item' => $item
        ];

        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('putItem', [$query])
            ->willThrowException($this->createMock(DynamoDbException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(PersistenceException::class);

        $this->itemManager->save($data, $partitionKey, $sortKey);
    }

    /**
     * @throws \AllCoin\Database\DynamoDb\Exception\PersistenceException
     */
    public function testSaveShouldBeOK(): void
    {
        $data = [
            'nullValue' => null
        ];
        $partitionKey = 'foo';
        $sortKey = 'bar';

        $dataExpected = [
            'nullValue' => '',
            ItemManager::PARTITION_KEY_NAME => $partitionKey,
            ItemManager::SORT_KEY_NAME => $sortKey
        ];

        $item = [];
        $this->marshalerService->expects($this->once())
            ->method('marshalItem')
            ->with($dataExpected)
            ->willReturn($item);

        $query = [
            'TableName' => $this->tableName,
            'Item' => $item
        ];

        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('putItem', [$query]);

        $this->logger->expects($this->never())
            ->method('error');

        $this->itemManager->save($data, $partitionKey, $sortKey);
    }

    public function testFetchAllWithMarshalValueErrorShouldThrowException(): void
    {
        $partitionKeyName = 'foo';

        $this->marshalerService->expects($this->once())
            ->method('marshalValue')
            ->with($partitionKeyName)
            ->willThrowException($this->createMock(MarshalerException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(ReadException::class);

        $this->dynamoDbClient->expects($this->never())
            ->method('__call');
        $this->marshalerService->expects($this->never())->method('unmarshalItem');

        $this->itemManager->fetchAll($partitionKeyName);
    }

    public function testFetchAllWithDynamoDdQueryErrorShouldThrowException(): void
    {
        $partitionKeyName = 'foo';

        $marshaledPartitionKey = ['foo' => 'bar'];
        $this->marshalerService->expects($this->once())
            ->method('marshalValue')
            ->with($partitionKeyName)
            ->willReturn($marshaledPartitionKey);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . " = :value",
            'ExpressionAttributeValues' => [':value' => $marshaledPartitionKey]
        ];

        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('query', [$query])
            ->willThrowException($this->createMock(DynamoDbException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(ReadException::class);

        $this->marshalerService->expects($this->never())->method('unmarshalItem');

        $this->itemManager->fetchAll($partitionKeyName);
    }

    public function testFetchAllWithMarshalItemErrorShouldThrowException(): void
    {
        $partitionKeyName = 'foo';

        $marshaledPartitionKey = ['foo' => 'bar'];
        $this->marshalerService->expects($this->once())
            ->method('marshalValue')
            ->with($partitionKeyName)
            ->willReturn($marshaledPartitionKey);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . " = :value",
            'ExpressionAttributeValues' => [':value' => $marshaledPartitionKey]
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

        $this->expectException(ReadException::class);
        $this->logger->expects($this->once())->method('error');

        $this->itemManager->fetchAll($partitionKeyName);
    }

    /**
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
     */
    public function testFetchAllShouldBeOK(): void
    {
        $partitionKeyName = 'foo';

        $marshaledPartitionKey = ['foo' => 'bar'];
        $this->marshalerService->expects($this->once())
            ->method('marshalValue')
            ->with($partitionKeyName)
            ->willReturn($marshaledPartitionKey);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . " = :value",
            'ExpressionAttributeValues' => [':value' => $marshaledPartitionKey]
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
        $items = $this->itemManager->fetchAll($partitionKeyName);

        $this->assertEquals($itemsExpected, $items);
    }
}
