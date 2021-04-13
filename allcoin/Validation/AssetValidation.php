<?php


namespace AllCoin\Validation;


use AllCoin\Dto\AssetRequestDto;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class AssetValidation
{
    #[ArrayShape([AssetRequestDto::NAME => "string"])]
    public function getPostRules(): array
    {
        return [
            AssetRequestDto::NAME => 'required|string'
        ];
    }

    #[Pure] #[ArrayShape([AssetRequestDto::NAME => "string"])]
    public function getPutRules(): array
    {
        return $this->getPostRules();
    }
}
