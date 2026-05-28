EXPLAIN ANALYZE
SELECT
    products.title,
    SUM(order_items.qty) AS total_qty
FROM order_items
         INNER JOIN products ON order_items.product_id = products.id
         INNER JOIN orders ON order_items.order_id = orders.id
WHERE orders.created_at >= :date_from --'2026-04-28' Если через phpStorm параметры писать выкинет ошибку
  AND orders.created_at < :date_to --'2026-05-29'
GROUP BY products.id, products.title
ORDER BY total_qty DESC
LIMIT 50;