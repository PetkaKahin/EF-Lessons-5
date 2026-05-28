<?php

declare(strict_types=1);

const USERS_COUNT = 50_000;
const PRODUCTS_COUNT = 20_000;
const ORDERS_COUNT = 100_000;
const ITEMS_IN_ORDER = 2;
const CREATED_AT_FROM = '2025-01-01 00:00:00+00';

$pdo = connectToDatabase();

try {
    seedDatabase($pdo);
} catch (Throwable $exception) {
    echo 'Seeding failed: ' . $exception->getMessage() . PHP_EOL;
    exit(1);
}

echo 'Seed complete' . PHP_EOL;

function connectToDatabase(): PDO
{
    return new PDO(
        getenv('DATABASE_DSN') ?: 'pgsql:host=postgres;port=5432;dbname=ef_lesson_5',
        getenv('DATABASE_USER') ?: 'app',
        getenv('DATABASE_PASSWORD') ?: 'app',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );
}

function seedDatabase(PDO $pdo): void
{
    mt_srand(42);
    $createdAtTo = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:sP');

    $pdo->beginTransaction();

    try {
        $userIds = seedUsers($pdo, $createdAtTo);
        $products = seedProducts($pdo, $createdAtTo);

        seedOrders($pdo, $userIds, $products, $createdAtTo);

        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

/**
 * @return list<int>
 */
function seedUsers(PDO $pdo, string $createdAtTo): array
{
    $insertUser = $pdo->prepare(
        'INSERT INTO users (email, name, created_at) VALUES (?, ?, ?) RETURNING id',
    );
    $userIds = [];

    for ($number = 1; $number <= USERS_COUNT; $number++) {
        $insertUser->execute([
            "user{$number}@example.test",
            "User {$number}",
            randomCreatedAt(CREATED_AT_FROM, $createdAtTo),
        ]);

        $userIds[] = (int) $insertUser->fetchColumn();
    }

    echo 'Users created: ' . USERS_COUNT . PHP_EOL;

    return $userIds;
}

/**
 * @return array{ids: list<int>, prices: array<int, float>}
 */
function seedProducts(PDO $pdo, string $createdAtTo): array
{
    $insertProduct = $pdo->prepare(
        'INSERT INTO products (sku, title, price, created_at) VALUES (?, ?, ?, ?) RETURNING id',
    );
    $productIds = [];
    $productPrices = [];

    for ($number = 1; $number <= PRODUCTS_COUNT; $number++) {
        $price = mt_rand(199, 99_999) / 100;

        $insertProduct->execute([
            "SKU-{$number}",
            "Product {$number}",
            $price,
            randomCreatedAt(CREATED_AT_FROM, $createdAtTo),
        ]);

        $productId = (int) $insertProduct->fetchColumn();
        $productIds[] = $productId;
        $productPrices[$productId] = $price;
    }

    echo 'Products created: ' . PRODUCTS_COUNT . PHP_EOL;

    return [
        'ids' => $productIds,
        'prices' => $productPrices,
    ];
}

/**
 * @param list<int> $userIds
 * @param array{
 *     ids: list<int>,
 *     prices: array<int, float>
 * } $products
 */
function seedOrders(PDO $pdo, array $userIds, array $products, string $createdAtTo): void
{
    $insertOrder = $pdo->prepare(
        'INSERT INTO orders (user_id, status, total_amount, created_at) VALUES (?, ?, ?, ?) RETURNING id',
    );
    $insertItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)',
    );
    $updateOrderTotal = $pdo->prepare(
        'UPDATE orders SET total_amount = ? WHERE id = ?',
    );
    $insertPayment = $pdo->prepare(
        'INSERT INTO payments (order_id, status, provider, created_at) VALUES (?, ?, ?, ?)',
    );
    $providers = ['stripe', 'paypal', 'bank_transfer'];

    for ($number = 1; $number <= ORDERS_COUNT; $number++) {
        $userId = $userIds[mt_rand(0, count($userIds) - 1)];
        $orderStatus = randomOrderStatus();
        $createdAt = randomCreatedAt(CREATED_AT_FROM, $createdAtTo);

        $insertOrder->execute([$userId, $orderStatus, '0.00', $createdAt]);
        $orderId = (int) $insertOrder->fetchColumn();
        $orderTotal = seedOrderItems($insertItem, $orderId, $products);

        $updateOrderTotal->execute([$orderTotal, $orderId]);
        seedPayment($insertPayment, $orderId, $orderStatus, $providers, $createdAt);
    }

    echo 'Orders created: ' . ORDERS_COUNT . PHP_EOL;
    echo 'Order items created: ' . ORDERS_COUNT * ITEMS_IN_ORDER . PHP_EOL;
    echo 'Payments created: ' . ORDERS_COUNT . PHP_EOL;
}

/**
 * @param array{
 *     ids: list<int>,
 *     prices: array<int, float>
 * } $products
 */
function seedOrderItems(PDOStatement $insertItem, int $orderId, array $products): float
{
    $orderTotal = 0.0;

    for ($item = 1; $item <= ITEMS_IN_ORDER; $item++) {
        $productId = $products['ids'][mt_rand(0, count($products['ids']) - 1)];
        $price = $products['prices'][$productId];
        $quantity = mt_rand(1, 4);

        $insertItem->execute([
            $orderId,
            $productId,
            $quantity,
            $price,
        ]);

        $orderTotal += $price * $quantity;
    }

    return $orderTotal;
}

/**
 * @param list<string> $providers
 */
function seedPayment(
    PDOStatement $insertPayment,
    int $orderId,
    string $orderStatus,
    array $providers,
    string $createdAt,
): void {
    $insertPayment->execute([
        $orderId,
        paymentStatus($orderStatus),
        $providers[mt_rand(0, count($providers) - 1)],
        $createdAt,
    ]);
}

function randomCreatedAt(string $from, string $to): string
{
    $fromTimestamp = (new DateTimeImmutable($from))->getTimestamp();
    $toTimestamp = (new DateTimeImmutable($to))->getTimestamp();

    if ($fromTimestamp > $toTimestamp) {
        throw new InvalidArgumentException('Created at start date must not be later than end date.');
    }

    $timestamp = mt_rand($fromTimestamp, $toTimestamp);

    return (new DateTimeImmutable('@' . $timestamp))->format('Y-m-d H:i:sP');
}

function randomOrderStatus(): string
{
    $roll = mt_rand(1, 100);

    if ($roll <= 70) {
        return 'paid';
    }

    if ($roll <= 85) {
        return 'new';
    }

    return 'cancelled';
}

function paymentStatus(string $orderStatus): string
{
    if ($orderStatus === 'paid') {
        return 'paid';
    }

    if ($orderStatus === 'new') {
        $rand = mt_rand(1, 100);

        if ($rand <= 50) {
            return 'paid';
        } elseif ($rand <= 85) {
            return 'failed';
        } else {
            return 'pending';
        }
    }

    return mt_rand(1, 100) <= 75 ? 'failed' : 'pending';
}