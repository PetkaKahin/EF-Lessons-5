EXPLAIN ANALYZE
SELECT
    users.name,
    SUM(orders.total_amount) as total_amount
FROM orders
    INNER JOIN users ON orders.user_id = users.id
WHERE orders.created_at >= NOW() - INTERVAL '30 days'
GROUP BY user_id, users.name
ORDER BY total_amount DESC
LIMIT 20