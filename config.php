<?php

declare(strict_types=1);

return [
    'APP_URL' => 'http://localhost:5173',
    'API_TOKEN' => '123',
    'CORS_ALLOWED_ORIGIN' => 'http://localhost:5173',
    'CORS_ALLOWED_METHODS' => 'GET, POST, PATCH, DELETE, OPTIONS',
    'CORS_ALLOWED_HEADERS' => 'Content-Type, Authorization, Idempotency-Key',

    'DATABASE_DSN' => 'pgsql:host=postgres;port=5432;dbname=ef_lesson_5',
    'DATABASE_USER' => 'app',
    'DATABASE_PASSWORD' => 'app',
    'MIGRATIONS_PATH' => __DIR__ . '/Infrastructure/DataBase/Migrations',
    'WEBHOOK_URL' => 'http://nginx/webhook-receiver',

    'DEBUG' => true,
];
