<?php


namespace App\Providers;


use Ajegu\BinanceSdk\Client;
use Illuminate\Support\ServiceProvider;

class BinanceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerBinanceAPIClient();
    }

    private function registerBinanceAPIClient(): void
    {
        $this->app->singleton(Client::class, function() {
            return Client::create();
        });
    }
}
