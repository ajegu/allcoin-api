<?php

/** @var Application $app */

use App\Lambda\BinanceAssetSyncLambda;
use Laravel\Lumen\Application;

$app = require __DIR__ . '/../bootstrap/app.php';

return $app->make(BinanceAssetSyncLambda::class);
