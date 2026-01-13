<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

// Boot the application
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

echo "Routes loaded:\n";
foreach ($app->make('router')->getRoutes() as $route) {
    $action = $route->getActionName();
    echo $route->uri() . ' -> ' . $action . PHP_EOL;
}
