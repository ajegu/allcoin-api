<?php


namespace AllCoin\Database\DynamoDb;


use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\Exception\PersistenceException;
use AllCoin\Database\DynamoDb\Exception\ReadException;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
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
     * @param string $partitionKeyName
     * @return array
     * @throws \AllCoin\Database\DynamoDb\Exception\ReadException
     */
    public function fetchAll(string $partitionKeyName): array
    {
        try {
            $value = $this->marshalerService->marshalValue($partitionKeyName);
        } catch (MarshalerException $exception) {
            $message = 'Cannot marshal the data to item.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'value' => $partitionKeyName
            ]);
            throw new ReadException($message);
        }
        $query = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' => self::PARTITION_KEY_NAME . " = :value",
            'ExpressionAttributeValues' => [':value' => $value]
        ];

        try {
            $result = $this->dynamoDbClient->query($query);
        } catch (DynamoDbException $exception) {
            $message = 'Cannot execute the query.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'query' => $query
            ]);
            throw new ReadException($message);
        }

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
}
