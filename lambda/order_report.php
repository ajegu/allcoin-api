<?php
/** @var Application $app */

use App\Lambda\OrderReportLambda;
use Laravel\Lumen\Application;

$app = require __DIR__ . '/../bootstrap/app.php';

return $app->make(OrderReportLambda::class)([]);
