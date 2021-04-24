<?php


namespace Test\AllCoin\Builder;


use AllCoin\Builder\OrderBuilder;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\UuidService;
use DateTime;
use Test\TestCase;

class OrderBuilderTest extends TestCase
{
    private OrderBuilder $orderBuilder;

    private DateTimeService $dateTimeService;
    private UuidService $uuidService;

    public function setUp(): void
    {
        $this->dateTimeService = $this->createMock(DateTimeService::class);
        $this->uuidService = $this->createMock(UuidService::class);

        $this->orderBuilder = new OrderBuilder(
            $this->dateTimeService,
            $this->uuidService,
        );
    }

    public function testBuildShouldBeOK(): void
    {
        $quantity = 1;
        $amount = 10;
        $direction = 'foo';
        $version = 'bar';

        $uuid = 'foo';
        $this->uuidService->expects($this->once())->method('generateUuid')->willReturn($uuid);

        $now = new DateTime();
        $this->dateTimeService->expects($this->once())->method('now')->willReturn($now);

        $order = $this->orderBuilder->build(
            $quantity,
            $amount,
            $direction,
            $version
        );

        $this->assertEquals($uuid, $order->getId());
        $this->assertEquals($quantity, $order->getQuantity());
        $this->assertEquals($amount, $order->getAmount());
        $this->assertEquals($direction, $order->getDirection());
        $this->assertEquals($now, $order->getCreatedAt());
        $this->assertEquals($version, $order->getVersion());
    }
}
