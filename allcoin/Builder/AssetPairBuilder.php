<?php


namespace AllCoin\Builder;


use AllCoin\Model\AssetPair;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\UuidService;

class AssetPairBuilder
{
    public function __construct(
        private UuidService $uuidService,
        private DateTimeService $dateTimeService
    )
    {
    }

    public function build(string $assetPairName): AssetPair
    {
        return new AssetPair(
            id: $this->uuidService->generateUuid(),
            name: $assetPairName,
            createdAt: $this->dateTimeService->now()
        );
    }
}
