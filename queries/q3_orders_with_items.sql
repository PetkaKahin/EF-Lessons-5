EXPLAIN ANALYSE

WITH last_orders AS (
    SELECT id, user_id, status, total_amount, created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 100
)
SELECT last_orders.id AS order_id,
    last_orders.status,
    last_orders.total_amount,
    products.title,
    order_items.qty,
    order_items.price,
    last_orders.created_at
FROM last_orders
    INNER JOIN order_items ON order_items.order_id = last_orders.id
    INNER JOIN products ON products.id = order_items.product_id
ORDER BY last_orders.created_at DESC, last_orders.id, products.title;
