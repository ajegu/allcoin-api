<?php


namespace AllCoin\Model;


class ClassMappingEnum
{
    const ASSET = 'asset';
    const ASSET_PAIR = 'assetPair';
    const ASSET_PAIR_PRICE = 'assetPairPrice';

    const CLASS_MAPPING = [
        Asset::class => self::ASSET,
        AssetPair::class => self::ASSET_PAIR,
        AssetPairPrice::class => self::ASSET_PAIR_PRICE,
    ];
}
