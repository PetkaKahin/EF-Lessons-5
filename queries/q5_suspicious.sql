EXPLAIN ANALYSE

WITH failed AS (
    SELECT orders.user_id,
        COUNT(*) OVER (
           PARTITION BY orders.user_id
           ORDER BY payments.created_at
           RANGE BETWEEN CURRENT ROW AND INTERVAL '24 hours' FOLLOWING
        ) AS failed_count
    FROM payments
        JOIN orders ON orders.id = payments.order_id
    WHERE payments.status = 'failed'
)
SELECT users.id, users.name, max(failed.failed_count) AS max_failed_24h
FROM users
    JOIN failed ON failed.user_id = users.id
WHERE failed.failed_count > 1 -- У меня при рандоме максимум 2 failed, поставлю его чтобы было видно, что работает
GROUP BY users.id, users.name;
