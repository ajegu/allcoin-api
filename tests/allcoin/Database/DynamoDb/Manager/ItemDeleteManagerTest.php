<?php


namespace Test\AllCoin\Database\DynamoDb\Manager;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\MarshalerService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class ItemDeleteManagerTest extends TestCase
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

    public function testDeleteWithMarshalValueErrorShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $sortKey = 'foo';

        $this->marshalerService->expects($this->once())
            ->method('marshalValue')
            ->with($partitionKey)
            ->willThrowException($this->createMock(MarshalerException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(ItemDeleteException::class);

        $this->dynamoDbClient->expects($this->never())
            ->method('__call');

        $this->itemManager->delete($partitionKey, $sortKey);
    }

    public function testDeleteWithDynamoDdQueryErrorShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $sortKey = 'bar';

        $marshaledPartitionKey = ['foo' => 'bar'];
        $marshaledSortKey = ['bar' => 'foo'];
        $this->marshalerService->expects($this->exactly(2))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$sortKey])
            ->willReturn($marshaledPartitionKey, $marshaledSortKey);

        $query = [
            'TableName' => $this->tableName,
            'Key' => [
                ItemManager::PARTITION_KEY_NAME => $marshaledPartitionKey,
                ItemManager::SORT_KEY_NAME => $marshaledSortKey,
            ]
        ];

        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('deleteItem', [$query])
            ->willThrowException($this->createMock(DynamoDbException::class));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(ItemDeleteException::class);

        $this->itemManager->delete($partitionKey, $sortKey);
    }

    public function testDeleteShouldBeOK(): void
    {
        $partitionKey = 'foo';
        $sortKey = 'bar';

        $marshaledPartitionKey = ['foo' => 'bar'];
        $marshaledSortKey = ['bar' => 'foo'];
        $this->marshalerService->expects($this->exactly(2))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$sortKey])
            ->willReturn($marshaledPartitionKey, $marshaledSortKey);

        $query = [
            'TableName' => $this->tableName,
            'Key' => [
                ItemManager::PARTITION_KEY_NAME => $marshaledPartitionKey,
                ItemManager::SORT_KEY_NAME => $marshaledSortKey,
            ]
        ];

        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('deleteItem', [$query]);

        $this->logger->expects($this->never())
            ->method('error');

        $this->itemManager->delete($partitionKey, $sortKey);
    }
}
