<?php


namespace App\Providers;


use AllCoin\Repository\AssetRepository;
use AllCoin\Repository\AssetRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AssetRepositoryInterface::class, AssetRepository::class);
    }
}
