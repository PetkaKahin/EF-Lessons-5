<?php

declare(strict_types=1);

$pdo = new PDO(
    getenv('DATABASE_DSN') ?: 'pgsql:host=postgres;port=5432;dbname=ef_lesson_5',
    getenv('DATABASE_USER') ?: 'app',
    getenv('DATABASE_PASSWORD') ?: 'app',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

$pdo->beginTransaction();

try {
    $pdo->exec(
        'TRUNCATE TABLE audit_log, payments, order_items, orders, products, users RESTART IDENTITY CASCADE',
    );
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, sprintf('Seed rollback failed: %s', $exception->getMessage()) . PHP_EOL);
    exit(1);
}

echo 'Seed data removed.' . PHP_EOL;
