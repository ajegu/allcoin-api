<?php


namespace AllCoin\Service;


use AllCoin\Exception\DateTimeServiceException;
use DateInterval;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;

class DateTimeService
{
    public function __construct(
        private LoggerInterface $logger
    )
    {
    }

    public function now(): DateTime
    {
        return new DateTime();
    }

    /**
     * @param DateTime $dateTime
     * @param string $duration
     * @return DateTime
     */
    public function sub(DateTime $dateTime, string $duration): DateTime
    {
        $date = $dateTime->format(DATE_RFC3339);
        $subDateTime = DateTime::createFromFormat(DATE_RFC3339, $date);
        try {
            $subDateTime->sub(
                new DateInterval($duration)
            );
        } catch (Exception $exception) {
            $message = 'Cannot sub the duration on given DateTime';
            $this->logger->error($message, [
                'message' => $exception->getMessage(),
                'duration' => $duration,
                'date' => $date
            ]);
            throw new DateTimeServiceException($message);
        }

        return $subDateTime;
    }
}
