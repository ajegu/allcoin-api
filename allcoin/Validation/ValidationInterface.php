<?php


namespace AllCoin\Validation;


interface ValidationInterface
{
    public function getPostRules(): array;

    public function getPutRules(): array;
}
