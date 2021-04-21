<?php
/** @var Application $app */

use App\Lambda\BinanceBuyOrderLambda;
use Laravel\Lumen\Application;

$app = require __DIR__ . '/../bootstrap/app.php';

return $app->make(BinanceBuyOrderLambda::class);
