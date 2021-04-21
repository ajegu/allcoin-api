<?php


namespace AllCoin\Model;


use AllCoin\Dto\RequestDtoInterface;
use DateTime;

class EventPrice implements EventInterface, RequestDtoInterface
{
    public function __construct(
        private string $name,
        private Asset $asset,
        private AssetPair $assetPair,
        private float $price,
        private DateTime $date,
        private string $percent
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
     * @return string
     */
    public function getPercent(): string
    {
        return $this->percent;
    }

    /**
     * @param string $percent
     */
    public function setPercent(string $percent): void
    {
        $this->percent = $percent;
    }


}
