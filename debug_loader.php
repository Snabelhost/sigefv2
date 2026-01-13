<?php
require 'vendor/autoload.php';

$reflector = new ReflectionClass(\Composer\Autoload\ClassLoader::class);
echo "ClassLoader file: " . $reflector->getFileName() . "\n";

echo "FilamentServiceProvider class exists: " . (class_exists('Filament\FilamentServiceProvider') ? 'YES' : 'NO') . "\n";

$loader = require 'vendor/autoload.php';
$prefixes = $loader->getPrefixesPsr4();
echo "Is Filament in PSR-4? " . (isset($prefixes['Filament\\']) ? 'YES' : 'NO') . "\n";
if (isset($prefixes['Filament\\'])) {
    print_r($prefixes['Filament\\']);
}
