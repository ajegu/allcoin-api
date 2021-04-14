<?php


namespace App\Providers;


use AllCoin\Database\DynamoDb\ItemManager;
use AllCoin\Database\DynamoDb\ItemManagerInterface;
use App\Exceptions\ServiceProviderException;
use Illuminate\Support\ServiceProvider;

class DynamoDbServiceProvider extends ServiceProvider
{
    /**
     * @throws ServiceProviderException
     */
    public function register(): void
    {
        $this->registerItemManagerInterface();
    }

    /**
     * @throws ServiceProviderException
     */
    private function registerItemManagerInterface(): void
    {
        $this->app->bind(ItemManagerInterface::class, ItemManager::class);

        $this->setDynamoDbTableName();
    }

    /**
     * @throws ServiceProviderException
     */
    private function setDynamoDbTableName(): void
    {
        $env = 'AWS_DDB_TABLE_NAME';
        if (!env($env)) {
            throw new ServiceProviderException(
                "You must defined the environment variable {$env}"
            );
        }

        $this->app->when(ItemManager::class)
            ->needs('$tableName')
            ->give(env($env));
    }
}
