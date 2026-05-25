<?php

declare(strict_types=1);

use Infrastructure\DataBase\MigrationRunner;
use Infrastructure\Kernel\ContainerFactory;

require __DIR__ . '/../../vendor/autoload.php';

$container = (new ContainerFactory())->create();

/** @var MigrationRunner $runner */
$runner = $container->get(MigrationRunner::class);
$appliedMigrations = $runner->run();

if ($appliedMigrations === []) {
    echo "No pending migrations." . PHP_EOL;
    exit(0);
}

foreach ($appliedMigrations as $migration) {
    echo "Applied migration: $migration" . PHP_EOL;
}

echo sprintf("Applied %d migration(s).", count($appliedMigrations)) . PHP_EOL;
