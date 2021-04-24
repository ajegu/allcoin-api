<?php
/** @var Application $app */

use App\Lambda\BinancePriceAnalyzerLambda;
use Laravel\Lumen\Application;

$app = require __DIR__ . '/../bootstrap/app.php';

return $app->make(BinancePriceAnalyzerLambda::class);
