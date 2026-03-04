<?php
require 'vendor/autoload.php';
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$conn = $container->get('doctrine.dbal.default_connection');
$sm = $conn->createSchemaManager();

$tables = $sm->listTableNames();
foreach ($tables as $table) {
    echo "Table: $table\n";
    $columns = $sm->listTableColumns($table);
    foreach ($columns as $column) {
        echo "  - " . $column->getName() . " (" . $column->getType()->getName() . ")\n";
    }
    echo "\n";
}
