<?php

$pdo = new PDO('pgsql:host=postgres;port=5432;dbname=ef_lesson_5', 'app', 'app');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$orderId = 0;
if (isset($argv[1])) {
    $orderId = (int) $argv[1];
}

if ($orderId <= 0) {
    echo "Укажи id заказа: php bin/pay_order.php <order_id>\n";
    exit(1);
}

$pid = getmypid();

try {
    payOrder($pdo, $orderId);
    echo "OK: заказ $orderId оплачен (pid $pid)\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "FAIL: " . $e->getMessage() . " (pid $pid)\n";
    exit(1);
}

function payOrder($pdo, $orderId)
{
    echo "[$orderId] Начинаю оплату, занимаю заказ\n";
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = :id FOR UPDATE");
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order === false) {
        throw new Exception("Заказ $orderId не найден");
    }

    if ($order['status'] !== 'new') {
        throw new Exception("Заказ $orderId уже нельзя оплатить (статус " . $order['status'] . ")");
    }

    echo "[$orderId] Заказ свободен, провожу оплату\n";

    $stmt = $pdo->prepare("INSERT INTO payments (order_id, status, provider)
        VALUES (:id, 'paid', 'test')
        ON CONFLICT (order_id) DO UPDATE SET status = 'paid'");
    $stmt->execute(['id' => $orderId]);

    $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = :id");
    $stmt->execute(['id' => $orderId]);

    $stmt = $pdo->prepare("INSERT INTO audit_log (entity_type, entity_id, action) VALUES ('order', :id, 'pay')");
    $stmt->execute(['id' => $orderId]);

    $pdo->commit();
    echo "[$orderId] Оплата прошла, сохраняю\n";
}