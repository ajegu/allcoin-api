<?php


namespace AllCoin\Validation;


use AllCoin\Dto\AssetRequestDto;
use BadMethodCallException;
use JetBrains\PhpStorm\ArrayShape;

class AssetValidation
{
    #[ArrayShape([AssetRequestDto::NAME => "string"])]
    public function getPostRules(): array
    {
        return [
            AssetRequestDto::NAME => 'required|string'
        ];
    }

    public function getPutRules(): array
    {
        throw new BadMethodCallException();
    }
}
