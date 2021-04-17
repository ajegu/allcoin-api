<?php


namespace App\Providers;

use AllCoin\Notification\Handler\PriceAnalyzerNotificationHandler;
use App\Exceptions\ServiceProviderException;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @throws ServiceProviderException
     */
    public function register(): void
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
}
