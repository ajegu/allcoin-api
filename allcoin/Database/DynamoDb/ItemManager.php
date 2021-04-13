<?php


namespace AllCoin\Database\DynamoDb;


use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\Exception\PersistenceException;
use AllCoin\Database\DynamoDb\Exception\ReadException;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Result;
use Psr\Log\LoggerInterface;

class ItemManager implements ItemManagerInterface
{
    const PARTITION_KEY_NAME = 'pk';
    const SORT_KEY_NAME = 'sk';

    /**
     * ItemManager constructor.
     * @param \Aws\DynamoDb\DynamoDbClient $dynamoDbClient
     * @param \AllCoin\Database\DynamoDb\MarshalerService $marshalerService
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $tableName
     */
    public function __construct(
        private DynamoDbClient $dynamoDbClient,
        private MarshalerService $marshalerService,
        private LoggerInterface $logger,
        private string $tableName
    )
    {
    }

    /**
     * @param array $data
     * @param string $partitionKey
     * @param string $sortKey
     * @throws \AllCoin\Database\DynamoDb\Exception\PersistenceException
     */
    public function save(array $data, string $partitionKey, string $sortKey): void
    {
        $data = $this->normalize($data);

        $data[self::PARTITION_KEY_NAME] = $partitionKey;
        $data[self::SORT_KEY_NAME] = $sortKey;

        try {
            $item = $this->marshalerService->marshalItem($data);
        } catch (MarshalerException $exception) {
            $message = 'Cannot marshal the data to item.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'data' => $data
            ]);
            throw new PersistenceException($message);
        }

        $query = [
            'TableName' => $this->tableName,
            'Item' => $item
        ];

        try {
            $this->dynamoDbClient->putItem($query);
        } catch (DynamoDbException $exception) {
            $message = 'Cannot save the item.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'query' => $query
            ]);
            throw new PersistenceException($message);
        }
    }

    /**
     * @param string $partitionKey
     * @return array
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
     */
    public function fetchAll(string $partitionKey): array
    {
        $value = $this->marshalValueForReadOperation($partitionKey);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' => self::PARTITION_KEY_NAME . " = :value",
            'ExpressionAttributeValues' => [':value' => $value]
        ];

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
                throw new ReadException($message);
            }
        }, $result->get('Items'));
    }

    /**
     * @param string $partitionKey
     * @param string $sortKey
     * @return array
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
     */
    public function fetchOne(string $partitionKey, string $sortKey): array
    {
        $partitionKeyValue = $this->marshalValueForReadOperation($partitionKey);
        $sortKeyValue = $this->marshalValueForReadOperation($sortKey);

        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' => self::PARTITION_KEY_NAME . ' = :partitionKeyValue and ' . self::SORT_KEY_NAME . ' = :sortKeyValue',
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
            throw new ReadException($message);
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
            throw new ReadException($message);
        }

        return $this->denormalize($item);
    }

    /**
     * @param array $data
     * @return array
     */
    private function normalize(array $data): array
    {
        foreach ($data as $key => $value) {

            if ($value === null) {
                $data[$key] = '';
            }
        }

        return $data;
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
     * @param mixed $value
     * @return array
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
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
            throw new ReadException($message);
        }
    }

    /**
     * @param array $query
     * @return \Aws\Result
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
     */
    public function queryForReadOperation(array $query): Result
    {
        try {
            return $this->dynamoDbClient->query($query);
        } catch (DynamoDbException $exception) {
            $message = 'Cannot execute the query.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'query' => $query
            ]);
            throw new ReadException($message);
        }
    }
}
