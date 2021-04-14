<?php


namespace AllCoin\Validation;


use AllCoin\Dto\AssetPairRequestDto;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class AssetPairValidation implements ValidationInterface
{
    /**
     * @return string[]
     */
    #[Pure] #[ArrayShape([AssetPairRequestDto::NAME => "string"])] public function getPutRules(): array
    {
        return $this->getPostRules();
    }

    /**
     * @return string[]
     */
    #[ArrayShape([AssetPairRequestDto::NAME => "string"])] public function getPostRules(): array
    {
        return [
            AssetPairRequestDto::NAME => 'required|string'
        ];
    }

}
