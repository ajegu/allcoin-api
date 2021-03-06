<?php


namespace AllCoin\Builder;


use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Model\EventPrice;
use DateTime;
use JetBrains\PhpStorm\Pure;

class EventPriceBuilder
{
    /**
     * @param string $name
     * @param Asset $asset
     * @param AssetPair $assetPair
     * @param AssetPairPrice $assetPairPrice
     * @param DateTime $dateTime
     * @param string $percent
     * @return EventPrice
     */
    #[Pure] public function build(string $name, Asset $asset, AssetPair $assetPair, AssetPairPrice $assetPairPrice, DateTime $dateTime, string $percent): EventPrice
    {
        return new EventPrice(
            name: $name,
            asset: $asset,
            assetPair: $assetPair,
            price: $assetPairPrice->getAskPrice(),
            date: $dateTime,
            percent: $percent
        );
    }
}
