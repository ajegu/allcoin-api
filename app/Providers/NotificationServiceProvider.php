<?php


namespace App\Providers;

use AllCoin\Notification\Handler\PriceAnalyzerNotificationHandler;
use AllCoin\Notification\Handler\TransactionAnalyzerNotificationHandler;
use App\Exceptions\ServiceProviderException;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * @throws ServiceProviderException
     */
    public function register(): void
    {
        $this->registerPriceAnalyzerNotificationHandler();
        $this->registerTransactionAnalyzerNotificationHandler();
    }

    /**
     * @throws ServiceProviderException
     */
    private function registerPriceAnalyzerNotificationHandler(): void
    {
        $env = 'AWS_SNS_TOPIC_PRICE_ANALYZER_ARN';
        if (!getenv($env)) {
            throw new ServiceProviderException(
                "You must defined the environment variable {$env}"
            );
        }

        $this->app->when(PriceAnalyzerNotificationHandler::class)
            ->needs('$topicArn')
            ->give(getenv($env));
    }

    /**
     * @throws ServiceProviderException
     */
    private function registerTransactionAnalyzerNotificationHandler(): void
    {
        $env = 'AWS_SNS_TOPIC_TRANSACTION_ANALYZER_ARN';
        if (!getenv($env)) {
            throw new ServiceProviderException(
                "You must defined the environment variable {$env}"
            );
        }

        $this->app->when(TransactionAnalyzerNotificationHandler::class)
            ->needs('$topicArn')
            ->give(getenv($env));
    }
}
