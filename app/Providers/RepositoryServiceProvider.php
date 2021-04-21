<?php


namespace App\Providers;


use AllCoin\Repository\AssetPairPriceRepository;
use AllCoin\Repository\AssetPairPriceRepositoryInterface;
use AllCoin\Repository\AssetPairRepository;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepository;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Repository\TransactionRepository;
use AllCoin\Repository\TransactionRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AssetRepositoryInterface::class, AssetRepository::class);
        $this->app->bind(AssetPairRepositoryInterface::class, AssetPairRepository::class);
        $this->app->bind(AssetPairPriceRepositoryInterface::class, AssetPairPriceRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }
}
