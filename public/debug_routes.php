<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Console\Kernel;

$app->make(Kernel::class)->bootstrap();

echo "<h1>Debug de Rotas - Pesquisa por 'login'</h1>";
echo "<table border='1'><tr><th>Método</th><th>URI</th><th>Nome</th><th>Ação</th></tr>";

foreach (Route::getRoutes() as $route) {
    if (str_contains($route->uri(), 'login') || $route->getName() === 'login') {
        echo "<tr>";
        echo "<td>" . implode('|', $route->methods()) . "</td>";
        echo "<td>" . $route->uri() . "</td>";
        echo "<td>" . ($route->getName() ?: '<em>sem nome</em>') . "</td>";
        echo "<td>" . $route->getActionName() . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

echo "<h1>Todas as rotas registradas no web.php</h1>";
echo "<ul>";
foreach (Route::getRoutes() as $route) {
    if ($route->getPrefix() === null || $route->getPrefix() === '') {
        echo "<li>" . $route->uri() . " (" . ($route->getName() ?: 'n/a') . ")</li>";
    }
}
echo "</ul>";
