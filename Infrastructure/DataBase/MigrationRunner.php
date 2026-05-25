<?php

declare(strict_types=1);

namespace Infrastructure\DataBase;

use Infrastructure\Config\Globals;
use PDO;
use RuntimeException;
use Throwable;

class MigrationRunner
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    /**
     * @return list<string> Names of migrations successfully applied during this run.
     */
    public function run(): array
    {
        $files = glob(Globals::MIGRATIONS_PATH . '/*.sql') ?: [];
        sort($files);

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS migrations (name TEXT PRIMARY KEY)');

        $findMigration = $this->pdo->prepare('SELECT 1 FROM migrations WHERE name = :name');
        $recordMigration = $this->pdo->prepare('INSERT INTO migrations (name) VALUES (:name)');
        $applied = [];

        foreach ($files as $file) {
            $name = basename($file);
            $findMigration->execute(['name' => $name]);

            if ($findMigration->fetchColumn() !== false) {
                continue;
            }

            $this->pdo->beginTransaction();

            try {
                $sql = file_get_contents($file);

                if ($sql === false) {
                    throw new RuntimeException("Unable to read migration file: $name");
                }

                $this->pdo->exec($sql);
                $recordMigration->execute(['name' => $name]);
                $this->pdo->commit();
                $applied[] = $name;
            } catch (Throwable $exception) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }

                throw $exception;
            }
        }

        return $applied;
    }
}
