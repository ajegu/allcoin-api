<?php


namespace AllCoin\Builder;


use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Model\EventTransaction;
use AllCoin\Service\DateTimeService;

class EventTransactionBuilder
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
    ): EventTransaction
    {
        return new EventTransaction(
            $eventName,
            $asset,
            $assetPair,
            $assetPairPrice,
            $this->dateTimeService->now(),
            $assetPairPrice->getBidPrice()
        );
    }
}
