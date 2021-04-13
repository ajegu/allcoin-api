<?php


namespace AllCoin\Service;


use Ramsey\Uuid\Uuid;

class UuidService
{
    public function generateUuid(): string
    {
        return Uuid::uuid4();
    }
}
