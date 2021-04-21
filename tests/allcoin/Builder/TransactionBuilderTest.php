<?php


namespace Test\AllCoin\Builder;


use AllCoin\Builder\TransactionBuilder;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\UuidService;
use DateTime;
use Test\TestCase;

class TransactionBuilderTest extends TestCase
{
    private TransactionBuilder $transactionBuilder;

    private DateTimeService $dateTimeService;
    private UuidService $uuidService;

    public function setUp(): void
    {
        $this->dateTimeService = $this->createMock(DateTimeService::class);
        $this->uuidService = $this->createMock(UuidService::class);

        $this->transactionBuilder = new TransactionBuilder(
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

        $transaction = $this->transactionBuilder->build(
            $quantity,
            $amount,
            $direction,
            $version
        );

        $this->assertEquals($uuid, $transaction->getId());
        $this->assertEquals($quantity, $transaction->getQuantity());
        $this->assertEquals($amount, $transaction->getAmount());
        $this->assertEquals($direction, $transaction->getDirection());
        $this->assertEquals($now, $transaction->getCreatedAt());
        $this->assertEquals($version, $transaction->getVersion());
    }
}
