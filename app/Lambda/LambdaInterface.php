<?php


namespace App\Lambda;


interface LambdaInterface
{
    public function __invoke(array $event): void;
}
