EXPLAIN ANALYSE
SELECT  products.title, orders.created_at FROM order_items
INNER JOIN orders ON order_items.order_id = orders.id
INNER JOIN products ON order_items.product_id = products.id
GROUP BY orders.created_at, products.title
ORDER BY orders.created_at DESC
LIMIT 100