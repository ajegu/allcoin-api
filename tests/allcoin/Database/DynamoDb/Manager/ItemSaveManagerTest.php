<?php


namespace Test\AllCoin\Database\DynamoDb\Manager;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\MarshalerService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class ItemSaveManagerTest extends TestCase
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

    /**
     * @throws ItemSaveException
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

        $this->expectException(ItemSaveException::class);

        $this->dynamoDbClient->expects($this->never())
            ->method('__call')
            ->with('putItem');

        $this->itemManager->save($data, $partitionKey, $sortKey);
    }

    /**
     * @throws ItemSaveException
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

        $this->expectException(ItemSaveException::class);

        $this->itemManager->save($data, $partitionKey, $sortKey);
    }

    /**
     * @throws ItemSaveException
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
}
