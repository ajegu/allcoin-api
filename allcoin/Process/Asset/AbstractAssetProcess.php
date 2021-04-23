<?php


namespace AllCoin\Process\Asset;


use AllCoin\DataMapper\AssetMapper;
use AllCoin\Exception\RequiredParameterException;
use AllCoin\Repository\AssetRepositoryInterface;

abstract class AbstractAssetProcess
{
    public function __construct(
        protected AssetRepositoryInterface $assetRepository,
        protected AssetMapper $assetMapper
    )
    {
    }

    /**
     * @param array $params
     * @return string
     */
    protected function getAssetId(array $params): string
    {
        return $params['id'] ?? throw new RequiredParameterException('The asset ID must be defined in $params');
    }
}
