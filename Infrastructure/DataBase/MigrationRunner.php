<?php

declare(strict_types=1);

namespace Infrastructure\DataBase;

use Infrastructure\Config\Globals;
use PDO;
use Throwable;

class MigrationRunner
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    public function run(): void
    {
        $files = glob(Globals::MIGRATIONS_PATH . '/*.sql') ?: [];
        sort($files);

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS migrations (name TEXT PRIMARY KEY)');

        foreach ($files as $file) {
            $name = basename($file);
            $quotedName = $this->pdo->quote($name);

            if ($this->pdo->query("SELECT name FROM migrations WHERE name = $quotedName")->fetchColumn()) {
                continue;
            }

            $this->pdo->beginTransaction();

            try {
                $this->pdo->exec((string) file_get_contents($file));
                $this->pdo->exec("INSERT INTO migrations (name) VALUES ($quotedName)");
                $this->pdo->commit();
            } catch (Throwable $exception) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }

                throw $exception;
            }
        }
    }
}
