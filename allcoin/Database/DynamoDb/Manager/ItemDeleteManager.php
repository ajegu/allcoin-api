<?php


namespace AllCoin\Database\DynamoDb\Manager;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\MarshalerService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Psr\Log\LoggerInterface;

class ItemDeleteManager
{
    public function __construct(
        private DynamoDbClient $dynamoDbClient,
        private MarshalerService $marshalerService,
        private LoggerInterface $logger,
        private string $tableName
    )
    {
    }

    /**
     * @param string $partitionKey
     * @param string $sortKey
     * @throws ItemDeleteException
     */
    public function delete(string $partitionKey, string $sortKey): void
    {
        $partitionKeyValue = $this->marshalValueForDeleteOperation($partitionKey);
        $sortKeyValue = $this->marshalValueForDeleteOperation($sortKey);

        $query = [
            'TableName' => $this->tableName,
            'Key' => [
                ItemManager::PARTITION_KEY_NAME => $partitionKeyValue,
                ItemManager::SORT_KEY_NAME => $sortKeyValue,
            ]
        ];

        try {
            $this->dynamoDbClient->deleteItem($query);
        } catch (DynamoDbException $exception) {
            $message = 'Cannot delete the item.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'query' => $query
            ]);
            throw new ItemDeleteException($message);
        }
    }

    /**
     * @param mixed $value
     * @return array
     * @throws ItemDeleteException
     */
    private function marshalValueForDeleteOperation(mixed $value): array
    {
        try {
            return $this->marshalerService->marshalValue($value);
        } catch (MarshalerException $exception) {
            $message = 'Cannot marshal the data to item.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'value' => $value
            ]);
            throw new ItemDeleteException($message);
        }
    }
}
