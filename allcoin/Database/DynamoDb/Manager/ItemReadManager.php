<?php


namespace AllCoin\Database\DynamoDb\Manager;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\MarshalerService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Result;
use Psr\Log\LoggerInterface;

class ItemReadManager
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
     * @return array
     * @throws ItemReadException
     */
    public function fetchAll(string $partitionKey): array
    {
        $value = $this->marshalValueForReadOperation($partitionKey);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . " = :value",
            'ExpressionAttributeValues' => [':value' => $value]
        ];

        return $this->executeFetchAllQuery($query);

    }

    /**
     * @param string $partitionKey
     * @param string $sortKey
     * @return array
     * @throws ItemReadException
     */
    public function fetchOne(string $partitionKey, string $sortKey): array
    {
        $partitionKeyValue = $this->marshalValueForReadOperation($partitionKey);
        $sortKeyValue = $this->marshalValueForReadOperation($sortKey);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . ItemManager::SORT_KEY_NAME . ' = :sortKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':sortKeyValue' => $sortKeyValue
            ]
        ];

        $result = $this->queryForReadOperation($query);

        $items = $result->get('Items');
        if (count($items) <> 1) {
            $message = 'The method fetchOne cannot read the result output';
            $this->logger->error($message, [
                'items' => $items
            ]);
            throw new ItemReadException($message);
        }

        $rawItem = $items[0];
        try {
            $item = $this->marshalerService->unmarshalItem($rawItem);
        } catch (MarshalerException $exception) {
            $message = 'Cannot unmarshal the item.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'item' => $rawItem
            ]);
            throw new ItemReadException($message);
        }

        return $this->denormalize($item);
    }

    /**
     * @param string $partitionKey
     * @param string $lsi
     * @param string $value
     * @return array
     * @throws ItemReadException
     */
    public function fetchAllOnLSI(string $partitionKey, string $lsiKeyName, string $lsiKey): array
    {
        $partitionKeyValue = $this->marshalValueForReadOperation($partitionKey);
        $lsiKeyValue = $this->marshalValueForReadOperation($lsiKey);

        $query = [
            'TableName' => $this->tableName,
            'IndexName' => ItemManager::LSI_INDEXES[$lsiKeyName],
            'KeyConditionExpression' => ItemManager::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . $lsiKeyName . ' = :lsiKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyValue,
                ':lsiKeyValue' => $lsiKeyValue
            ]
        ];

        return $this->executeFetchAllQuery($query);
    }

    /**
     * @param string $partitionKey
     * @param string $start
     * @param string $end
     * @return array
     * @throws ItemReadException
     */
    public function fetchAllBetween(string $partitionKey, string $start, string $end): array
    {
        $partitionKeyValue = $this->marshalValueForReadOperation($partitionKey);
        $startValue = $this->marshalValueForReadOperation($start);
        $endValue = $this->marshalValueForReadOperation($end);

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

        return $this->executeFetchAllQuery($query);
    }


    /**
     * @param array $query
     * @return Result
     * @throws ItemReadException
     */
    private function queryForReadOperation(array $query): Result
    {
        try {
            return $this->dynamoDbClient->query($query);
        } catch (DynamoDbException $exception) {
            $message = 'Cannot execute the query.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'query' => $query
            ]);
            throw new ItemReadException($message);
        }
    }

    /**
     * @param array $item
     * @return array
     */
    private function denormalize(array $item): array
    {
        foreach ($item as $key => $value) {
            if ('' === $value) {
                $item[$key] = null;
            }
        }

        return $item;
    }

    /**
     * @param array $query
     * @return array
     * @throws ItemReadException
     */
    private function executeFetchAllQuery(array $query): array
    {
        $result = $this->queryForReadOperation($query);

        return array_map(function (array $item) {
            try {
                $item = $this->marshalerService->unmarshalItem($item);
                return $this->denormalize($item);
            } catch (MarshalerException $exception) {
                $message = 'Cannot unmarshal the item.';
                $this->logger->error($message, [
                    'exception' => $exception->getMessage(),
                    'item' => $item
                ]);
                throw new ItemReadException($message);
            }
        }, $result->get('Items'));
    }

    /**
     * @param mixed $value
     * @return array
     * @throws ItemReadException
     */
    private function marshalValueForReadOperation(mixed $value): array
    {
        try {
            return $this->marshalerService->marshalValue($value);
        } catch (MarshalerException $exception) {
            $message = 'Cannot marshal the data to item.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'value' => $value
            ]);
            throw new ItemReadException($message);
        }
    }
}
