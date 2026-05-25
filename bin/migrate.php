<?php

declare(strict_types=1);

$pdo = new PDO(
    getenv('DATABASE_DSN') ?: 'pgsql:host=postgres;port=5432;dbname=ef_lesson_5',
    getenv('DATABASE_USER') ?: 'app',
    getenv('DATABASE_PASSWORD') ?: 'app',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

$files = glob(dirname(__DIR__) . '/migrations/*.sql') ?: [];
sort($files);

if ($files === []) {
    fwrite(STDERR, 'No SQL migrations found.' . PHP_EOL);
    exit(1);
}

$pdo->beginTransaction();

try {
    foreach ($files as $file) {
        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException(sprintf('Unable to read migration: %s', basename($file)));
        }

        $pdo->exec($sql);
        echo sprintf('Executed: %s', basename($file)) . PHP_EOL;
    }

    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, sprintf('Migration failed: %s', $exception->getMessage()) . PHP_EOL);
    exit(1);
}

echo sprintf('Executed %d migration(s).', count($files)) . PHP_EOL;
