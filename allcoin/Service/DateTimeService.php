<?php


namespace AllCoin\Service;


use DateTime;

class DateTimeService
{
    public function now(): DateTime
    {
        return new DateTime();
    }
}
