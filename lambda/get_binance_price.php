<?php
/** @var Application $app */

use App\Lambda\GetBinancePriceLambda;
use Laravel\Lumen\Application;

$app = require __DIR__ . '/../bootstrap/app.php';

return $app->make(GetBinancePriceLambda::class);
