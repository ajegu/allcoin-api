<?php
/** @var Application $app */

use App\Lambda\BinanceSyncPriceLambda;
use Laravel\Lumen\Application;

$app = require __DIR__ . '/../bootstrap/app.php';

return $app->make(BinanceSyncPriceLambda::class);
