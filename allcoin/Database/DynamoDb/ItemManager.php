<?php


namespace AllCoin\Database\DynamoDb;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\Manager\ItemDeleteManager;
use AllCoin\Database\DynamoDb\Manager\ItemReadManager;
use AllCoin\Database\DynamoDb\Manager\ItemSaveManager;
use Aws\DynamoDb\DynamoDbClient;
use Psr\Log\LoggerInterface;

class ItemManager implements ItemManagerInterface
{
    const PARTITION_KEY_NAME = 'pk';
    const SORT_KEY_NAME = 'sk';

    const LSI_1 = 'lsi1';

    const LSI_INDEXES = [
        self::LSI_1 => 'pk-lsi1-index'
    ];

    /**
     * ItemManager constructor.
     * @param DynamoDbClient $dynamoDbClient
     * @param MarshalerService $marshalerService
     * @param LoggerInterface $logger
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
     * @throws ItemSaveException
     */
    public function save(array $data, string $partitionKey, string $sortKey): void
    {
        $itemManager = new ItemSaveManager(
            $this->dynamoDbClient,
            $this->marshalerService,
            $this->logger,
            $this->tableName
        );

        $itemManager->save($data, $partitionKey, $sortKey);
    }

    /**
     * @param string $partitionKey
     * @return array
     * @throws ItemReadException
     */
    public function fetchAll(string $partitionKey): array
    {
        $itemManager = new ItemReadManager(
            $this->dynamoDbClient,
            $this->marshalerService,
            $this->logger,
            $this->tableName
        );

        return $itemManager->fetchAll($partitionKey);
    }

    /**
     * @param string $partitionKey
     * @param string $sortKey
     * @return array
     * @throws ItemReadException
     */
    public function fetchOne(string $partitionKey, string $sortKey): array
    {
        $itemManager = new ItemReadManager(
            $this->dynamoDbClient,
            $this->marshalerService,
            $this->logger,
            $this->tableName
        );

        return $itemManager->fetchOne($partitionKey, $sortKey);
    }

    /**
     * @param string $partitionKey
     * @param string $sortKey
     * @throws ItemDeleteException
     */
    public function delete(string $partitionKey, string $sortKey): void
    {
        $itemManager = new ItemDeleteManager(
            $this->dynamoDbClient,
            $this->marshalerService,
            $this->logger,
            $this->tableName
        );
        $itemManager->delete($partitionKey, $sortKey);

    }

    /**
     * @param string $partitionKey
     * @param string $lsiKeyName
     * @param string $lsiKey
     * @return array
     * @throws ItemReadException
     */
    public function fetchAllOnLSI(string $partitionKey, string $lsiKeyName, string $lsiKey): array
    {
        $itemManager = new ItemReadManager(
            $this->dynamoDbClient,
            $this->marshalerService,
            $this->logger,
            $this->tableName
        );

        return $itemManager->fetchAllOnLSI($partitionKey, $lsiKeyName, $lsiKey);
    }
}
