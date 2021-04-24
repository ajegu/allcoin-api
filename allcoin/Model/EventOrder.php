<?php


namespace AllCoin\Model;


use DateTime;

class EventOrder implements EventInterface
{
    public function __construct(
        private string $name,
        private Asset $asset,
        private AssetPair $assetPair,
        private AssetPairPrice $assetPairPrice,
        private DateTime $date,
        private float $price
    )
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Asset
     */
    public function getAsset(): Asset
    {
        return $this->asset;
    }

    /**
     * @param Asset $asset
     */
    public function setAsset(Asset $asset): void
    {
        $this->asset = $asset;
    }

    /**
     * @return AssetPair
     */
    public function getAssetPair(): AssetPair
    {
        return $this->assetPair;
    }

    /**
     * @param AssetPair $assetPair
     */
    public function setAssetPair(AssetPair $assetPair): void
    {
        $this->assetPair = $assetPair;
    }

    /**
     * @return AssetPairPrice
     */
    public function getAssetPairPrice(): AssetPairPrice
    {
        return $this->assetPairPrice;
    }

    /**
     * @param AssetPairPrice $assetPairPrice
     */
    public function setAssetPairPrice(AssetPairPrice $assetPairPrice): void
    {
        $this->assetPairPrice = $assetPairPrice;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

}
