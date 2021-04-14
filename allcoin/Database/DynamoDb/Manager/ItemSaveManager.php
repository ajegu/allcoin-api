<?php


namespace AllCoin\Database\DynamoDb\Manager;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\MarshalerService;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Psr\Log\LoggerInterface;

class ItemSaveManager
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
     * @param array $data
     * @param string $partitionKey
     * @param string $sortKey
     * @throws ItemSaveException
     */
    public function save(array $data, string $partitionKey, string $sortKey): void
    {
        $data = $this->normalize($data);

        $data[ItemManager::PARTITION_KEY_NAME] = $partitionKey;
        $data[ItemManager::SORT_KEY_NAME] = $sortKey;

        try {
            $item = $this->marshalerService->marshalItem($data);
        } catch (MarshalerException $exception) {
            $message = 'Cannot marshal the data to item.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'data' => $data
            ]);
            throw new ItemSaveException($message);
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
            throw new ItemSaveException($message);
        }
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
}
