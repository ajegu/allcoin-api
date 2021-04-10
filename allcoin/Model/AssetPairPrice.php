<?php


namespace AllCoin\Model;


class AssetPairPrice implements ModelInterface
{
    private ?AssetPair $assetPair;

    public function __construct(
        private float $bidPrice,
        private float $askPrice,
    )
    {
    }

    /**
     * @return AssetPair|null
     */
    public function getAssetPair(): ?AssetPair
    {
        return $this->assetPair;
    }

    /**
     * @param AssetPair|null $assetPair
     */
    public function setAssetPair(?AssetPair $assetPair): void
    {
        $this->assetPair = $assetPair;
    }

    /**
     * @return float
     */
    public function getBidPrice(): float
    {
        return $this->bidPrice;
    }

    /**
     * @param float $bidPrice
     */
    public function setBidPrice(float $bidPrice): void
    {
        $this->bidPrice = $bidPrice;
    }

    /**
     * @return float
     */
    public function getAskPrice(): float
    {
        return $this->askPrice;
    }

    /**
     * @param float $askPrice
     */
    public function setAskPrice(float $askPrice): void
    {
        $this->askPrice = $askPrice;
    }


}
