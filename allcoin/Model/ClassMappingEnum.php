<?php


namespace AllCoin\Model;


class ClassMappingEnum
{
    const ASSET = 'asset';

    const CLASS_MAPPING = [
        Asset::class => self::ASSET
    ];
}
