<?php


namespace Test\AllCoin\Database\DynamoDb\Manager\ItemReadManager;


use AllCoin\Database\DynamoDb\Exception\ItemNotFoundException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\MarshalerService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Result;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class FetchOneOnLSITest extends TestCase
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

    public function testFetchOneOnLSIWithMarshalValueErrorShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $lsiKeyName = ItemManager::LSI_1;
        $lsiKey = 'baz';

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

        $this->itemManager->fetchOneOnLSI($partitionKey, $lsiKeyName, $lsiKey);
    }

    public function testFetchOneOnLSIWithDynamoDdQueryErrorShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $lsiKeyName = ItemManager::LSI_1;
        $lsiKey = 'baz';

        $partitionKeyValue = ['S' => $partitionKey];
        $lsiKeyValue = ['S' => $lsiKey];
        $this->marshalerService->expects($this->exactly(2))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$lsiKey])
            ->willReturn($partitionKeyValue, $lsiKeyValue);

        $query = [
            'TableName' => $this->tableName,
            'IndexName' => ItemManager::LSI_INDEXES[$lsiKeyName],
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . $lsiKeyName . ' = :lsiKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':lsiKeyValue' => $lsiKeyValue
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

        $this->itemManager->fetchOneOnLSI($partitionKey, $lsiKeyName, $lsiKey);
    }

    /**
     * @throws ItemReadException
     */
    public function testFetchOneOnLSIWithNoItemShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $lsiKeyName = ItemManager::LSI_1;
        $lsiKey = 'baz';

        $partitionKeyValue = ['S' => $partitionKey];
        $lsiKeyValue = ['S' => $lsiKey];
        $this->marshalerService->expects($this->exactly(2))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$lsiKey])
            ->willReturn($partitionKeyValue, $lsiKeyValue);

        $query = [
            'TableName' => $this->tableName,
            'IndexName' => ItemManager::LSI_INDEXES[$lsiKeyName],
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . $lsiKeyName . ' = :lsiKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':lsiKeyValue' => $lsiKeyValue
            ]
        ];

        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('get')
            ->with('Items')
            ->willReturn([]);
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('query', [$query])
            ->willReturn($result);

        $this->expectException(ItemNotFoundException::class);

        $this->logger->expects($this->never())->method('error');
        $this->marshalerService->expects($this->never())->method('unmarshalItem');

        $this->itemManager->fetchOneOnLSI($partitionKey, $lsiKeyName, $lsiKey);
    }

    public function testFetchOneOnLSIWithItemCountErrorShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $lsiKeyName = ItemManager::LSI_1;
        $lsiKey = 'baz';

        $partitionKeyValue = ['S' => $partitionKey];
        $lsiKeyValue = ['S' => $lsiKey];
        $this->marshalerService->expects($this->exactly(2))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$lsiKey])
            ->willReturn($partitionKeyValue, $lsiKeyValue);

        $query = [
            'TableName' => $this->tableName,
            'IndexName' => ItemManager::LSI_INDEXES[$lsiKeyName],
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . $lsiKeyName . ' = :lsiKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':lsiKeyValue' => $lsiKeyValue
            ]
        ];

        $item = ['foo' => ''];
        $result = $this->createMock(Result::class);
        $result->expects($this->once())
            ->method('get')
            ->with('Items')
            ->willReturn([$item, $item]);
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with('query', [$query])
            ->willReturn($result);

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(ItemReadException::class);

        $this->marshalerService->expects($this->never())->method('unmarshalItem');

        $this->itemManager->fetchOneOnLSI($partitionKey, $lsiKeyName, $lsiKey);
    }

    public function testFetchOneOnLSIWithMarshalItemErrorShouldThrowException(): void
    {
        $partitionKey = 'foo';
        $lsiKeyName = ItemManager::LSI_1;
        $lsiKey = 'baz';

        $partitionKeyValue = ['S' => $partitionKey];
        $lsiKeyValue = ['S' => $lsiKey];
        $this->marshalerService->expects($this->exactly(2))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$lsiKey])
            ->willReturn($partitionKeyValue, $lsiKeyValue);

        $query = [
            'TableName' => $this->tableName,
            'IndexName' => ItemManager::LSI_INDEXES[$lsiKeyName],
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . $lsiKeyName . ' = :lsiKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':lsiKeyValue' => $lsiKeyValue
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

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(ItemReadException::class);

        $this->itemManager->fetchOneOnLSI($partitionKey, $lsiKeyName, $lsiKey);
    }

    /**
     * @throws ItemReadException
     */
    public function testFetchOneOnLSIShouldBeOK(): void
    {
        $partitionKey = 'foo';
        $lsiKeyName = ItemManager::LSI_1;
        $lsiKey = 'baz';

        $partitionKeyValue = ['S' => $partitionKey];
        $lsiKeyValue = ['S' => $lsiKey];
        $this->marshalerService->expects($this->exactly(2))
            ->method('marshalValue')
            ->withConsecutive([$partitionKey], [$lsiKey])
            ->willReturn($partitionKeyValue, $lsiKeyValue);

        $query = [
            'TableName' => $this->tableName,
            'IndexName' => ItemManager::LSI_INDEXES[$lsiKeyName],
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . $lsiKeyName . ' = :lsiKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':lsiKeyValue' => $lsiKeyValue
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

        $this->logger->expects($this->never())
            ->method('error');

        $this->itemManager->fetchOneOnLSI($partitionKey, $lsiKeyName, $lsiKey);
    }

}
