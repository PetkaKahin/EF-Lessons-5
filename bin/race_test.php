<?php

$pdo = new PDO('pgsql:host=postgres;port=5432;dbname=ef_lesson_5', 'app', 'app');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$attempts = 10;
$script = __DIR__ . '/pay_order.php';

$orderId = (int) $pdo->query("INSERT INTO orders (user_id, status, total_amount)
    VALUES ((SELECT id FROM users ORDER BY id LIMIT 1), 'new', 100)
    RETURNING id")->fetchColumn();

echo "Тестовый заказ №$orderId создан\n";
echo "Запускаем $attempts параллельных попыток оплаты\n\n";

$procs = [];
$pipes = [];
for ($i = 0; $i < $attempts; $i++) {
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $procs[$i] = proc_open("php $script $orderId", $descriptors, $pipes[$i]);
}

$ok = 0;
$failed = 0;
for ($i = 0; $i < $attempts; $i++) {
    $out = stream_get_contents($pipes[$i][1]);
    fclose($pipes[$i][1]);
    fclose($pipes[$i][2]);
    $code = proc_close($procs[$i]);

    $result = '';
    foreach (explode("\n", $out) as $line) {
        if (str_starts_with($line, 'OK') || str_starts_with($line, 'FAIL')) {
            $result = $line;
        }
    }

    echo "[$i] $result\n";

    if ($code === 0) {
        $ok++;
    } else {
        $failed++;
    }
}

echo "\nУспешно оплатили: $ok, отклонено: $failed\n";