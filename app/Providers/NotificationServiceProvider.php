<?php


namespace App\Providers;

use AllCoin\Notification\Handler\OrderAnalyzerNotificationHandler;
use AllCoin\Notification\Handler\PriceAnalyzerNotificationHandler;
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
        $this->registerOrderAnalyzerNotificationHandler();
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
    private function registerOrderAnalyzerNotificationHandler(): void
    {
        $env = 'AWS_SNS_TOPIC_TRANSACTION_ANALYZER_ARN';
        if (!getenv($env)) {
            throw new ServiceProviderException(
                "You must defined the environment variable {$env}"
            );
        }

        $this->app->when(OrderAnalyzerNotificationHandler::class)
            ->needs('$topicArn')
            ->give(getenv($env));
    }
}
