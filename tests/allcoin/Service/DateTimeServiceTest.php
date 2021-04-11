<?php


namespace Test\AllCoin\Service;


use AllCoin\Service\DateTimeService;
use Test\TestCase;

class DateTimeServiceTest extends TestCase
{
    private DateTimeService $dateTimeService;

    public function setUp(): void
    {
        $this->dateTimeService = new DateTimeService();
    }

    public function testNowShouldBeOK(): void
    {
        $this->assertNotEmpty(
            $this->dateTimeService->now()
        );
    }
}
