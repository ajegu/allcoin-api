<?php
/** @var Application $app */

use App\Lambda\PriceAnalyzerLambda;
use Laravel\Lumen\Application;

$app = require __DIR__ . '/../bootstrap/app.php';

return $app->make(PriceAnalyzerLambda::class);
