<?php


namespace App\Providers;


use App\Exceptions\ServiceProviderException;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\ServiceProvider;

class AwsServiceProvider extends ServiceProvider
{
    /**
     * @throws \App\Exceptions\ServiceProviderException
     */
    public function register(): void
    {
        $env = 'AWS_DEFAULT_REGION';
        if (!env($env)) {
            throw new ServiceProviderException(
                "You must defined the environment variable {$env}"
            );
        }

        $args = [
            'region' => env($env),
            'version' => 'latest'
        ];

        $this->registerAwsDynamoDbClient($args);
    }

    private function registerAwsDynamoDbClient($args): void
    {
        $this->app->singleton(DynamoDbClient::class, function () use ($args) {
            return new DynamoDbClient($args);
        });
    }
}
