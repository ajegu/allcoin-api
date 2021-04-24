<?php


namespace AllCoin\Builder;


use AllCoin\Model\Order;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\UuidService;

class OrderBuilder
{
    public function __construct(
        private DateTimeService $dateTimeService,
        private UuidService $uuidService
    )
    {
    }

    public function build(
        float $quantity,
        float $amount,
        string $direction,
        string $version = ''
    ): Order
    {
        return new Order(
            id: $this->uuidService->generateUuid(),
            quantity: $quantity,
            amount: $amount,
            direction: $direction,
            createdAt: $this->dateTimeService->now(),
            version: $version
        );
    }
}
