<?php


namespace AllCoin\Builder;


use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Model\EventOrder;
use AllCoin\Service\DateTimeService;

class EventOrderBuilder
{
    public function __construct(
        private DateTimeService $dateTimeService
    )
    {
    }

    public function build(
        string $eventName,
        Asset $asset,
        AssetPair $assetPair,
        AssetPairPrice $assetPairPrice,
    ): EventOrder
    {
        return new EventOrder(
            $eventName,
            $asset,
            $assetPair,
            $assetPairPrice,
            $this->dateTimeService->now(),
            $assetPairPrice->getBidPrice()
        );
    }
}
