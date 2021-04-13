<?php


namespace Test\AllCoin\Service;


use AllCoin\Service\UuidService;
use Test\TestCase;

class UuidServiceTest extends TestCase
{
    private UuidService $uuidService;

    public function setUp(): void
    {
        $this->uuidService = new UuidService();
    }

    public function testGenerateUuidShouldBeOK(): void
    {
        $this->assertNotEmpty(
            $this->uuidService->generateUuid()
        );

        $this->assertNotEquals(
            $this->uuidService->generateUuid(),
            $this->uuidService->generateUuid()
        );
    }
}
