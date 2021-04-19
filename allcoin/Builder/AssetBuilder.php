<?php


namespace AllCoin\Builder;


use AllCoin\Model\Asset;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\UuidService;

class AssetBuilder
{
    public function __construct(
        private UuidService $uuidService,
        private DateTimeService $dateTimeService
    )
    {
    }

    public function build(string $name): Asset
    {
        return new Asset(
            id: $this->uuidService->generateUuid(),
            name: $name,
            createdAt: $this->dateTimeService->now()
        );
    }
}
