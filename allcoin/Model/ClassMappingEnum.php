<?php


namespace AllCoin\Model;


class ClassMappingEnum
{
    const ASSET = 'asset';
    const ASSET_PAIR = 'assetPair';

    const CLASS_MAPPING = [
        Asset::class => self::ASSET,
        AssetPair::class => self::ASSET_PAIR,
    ];
}
