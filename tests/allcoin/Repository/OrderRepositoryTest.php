<?php


namespace Test\AllCoin\Repository;


use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\ItemManagerInterface;
use AllCoin\Model\ClassMappingEnum;
use AllCoin\Model\Order;
use AllCoin\Repository\OrderRepository;
use AllCoin\Service\SerializerService;
use DateTime;
use Test\TestCase;

class OrderRepositoryTest extends TestCase
{
    private OrderRepository $orderRepository;

    private ItemManagerInterface $itemManager;
    private SerializerService $serializerService;

    public function setUp(): void
    {
        $this->itemManager = $this->createMock(ItemManagerInterface::class);
        $this->serializerService = $this->createMock(SerializerService::class);

        $this->orderRepository = new OrderRepository(
            $this->itemManager,
            $this->serializerService
        );
    }

    /**
     * @throws ItemSaveException
     */
    public function testSaveShouldBeOK(): void
    {
        $order = $this->createMock(Order::class);
        $version = 'bar';
        $order->expects($this->once())->method('getVersion')->willReturn($version);
        $createdAt = new DateTime();
        $order->expects($this->once())->method('getCreatedAt')->willReturn($createdAt);
        $orderId = 'baz';
        $order->expects($this->once())->method('getId')->willReturn($orderId);

        $assetPairId = 'foo';

        $data = [];
        $this->serializerService->expects($this->once())
            ->method('normalizeModel')
            ->with($order)
            ->willReturn($data);

        $data[ItemManager::LSI_1] = $assetPairId;
        $data[ItemManager::LSI_2] = $version;
        $data[ItemManager::LSI_4] = $createdAt->getTimestamp();

        $this->itemManager->expects($this->once())
            ->method('save')
            ->with(
                data: $data,
                partitionKey: ClassMappingEnum::CLASS_MAPPING[Order::class],
                sortKey: $orderId
            );

        $this->orderRepository->save($order, $assetPairId);
    }
}
