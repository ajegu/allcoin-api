<?php


namespace Test\AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\ItemManagerInterface;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Model\Transaction;
use AllCoin\Repository\TransactionRepository;
use AllCoin\Service\SerializerService;
use DateTime;
use Test\TestCase;

class TransactionRepositoryTest extends TestCase
{
    private TransactionRepository $transactionRepository;

    private ItemManagerInterface $itemManager;
    private SerializerService $serializerService;

    public function setUp(): void
    {
        $this->itemManager = $this->createMock(ItemManagerInterface::class);
        $this->serializerService = $this->createMock(SerializerService::class);

        $this->transactionRepository = new TransactionRepository(
            $this->itemManager,
            $this->serializerService
        );
    }

    /**
     * @throws ItemSaveException
     */
    public function testSaveShouldBeOK(): void
    {
        $transaction = $this->createMock(Transaction::class);
        $version = 'bar';
        $transaction->expects($this->once())->method('getVersion')->willReturn($version);
        $createdAt = new DateTime();
        $transaction->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);
        $transactionId = 'baz';
        $transaction->expects($this->once())->method('getId')->willReturn($transactionId);

        $assetPairId = 'foo';

        $data = [];
        $this->serializerService->expects($this->once())
            ->method('normalizeModel')
            ->with($transaction)
            ->willReturn($data);

        $data[ItemManager::LSI_1] = $assetPairId;
        $data[ItemManager::LSI_2] = $version;
        $data[ItemManager::LSI_4] = $createdAt->getTimestamp();

        $this->itemManager->expects($this->once())
            ->method('save')
            ->with(
                data: $data,
                partitionKey: ClassMappingEnum::CLASS_MAPPING[Transaction::class],
                sortKey: $transactionId
            );

        $this->transactionRepository->save($transaction, $assetPairId);
    }
}
