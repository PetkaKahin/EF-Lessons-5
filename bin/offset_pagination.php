<?php

$pdo = new PDO('pgsql:host=postgres;port=5432;dbname=ef_lesson_5', 'app', 'app');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pageSize = 10;
$pages = [1, 500, 2000];

$sql = file_get_contents(__DIR__ . '/../queries/offset_pagination.sql');

foreach ($pages as $page) {
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('page', $page, PDO::PARAM_INT);
    $stmt->bindValue('page_size', $pageSize, PDO::PARAM_INT);

    $startedAt = microtime(true);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $time = round((microtime(true) - $startedAt) * 1000, 2);

    echo "page $page, page_size $pageSize, rows " . count($orders) . ", time {$time}ms\n";
}
