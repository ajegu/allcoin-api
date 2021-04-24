<?php
/** @var Application $app */

use App\Lambda\BinanceOrderAnalyzerLambda;
use Laravel\Lumen\Application;

$app = require __DIR__ . '/../bootstrap/app.php';

return $app->make(BinanceOrderAnalyzerLambda::class);
