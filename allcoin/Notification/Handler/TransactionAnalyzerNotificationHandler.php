<?php


namespace AllCoin\Notification\Handler;

use JetBrains\PhpStorm\Pure;

class TransactionAnalyzerNotificationHandler extends AbstractNotificationHandler
{
    #[Pure] public function __construct(
        private string $topicArn,
        private SnsHandler $snsHandler
    )
    {
        parent::__construct(
            $this->topicArn,
            $this->snsHandler
        );
    }
}
