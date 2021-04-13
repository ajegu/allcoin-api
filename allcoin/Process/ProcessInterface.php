<?php


namespace AllCoin\Process;


use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;

interface ProcessInterface
{
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface;
}
