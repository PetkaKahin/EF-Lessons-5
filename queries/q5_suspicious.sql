EXPLAIN ANALYSE
-- По ТЗ сказано, что надо 3 failed, но вероятность очень низка, можно уменьшить для проверки

-- За последние сутки
-- SELECT users.name, count(payments.id) as failed_count FROM payments
-- INNER JOIN orders ON payments.order_id = orders.id
-- INNER JOIN users ON orders.user_id = users.id
-- WHERE payments.status = 'failed'
--   AND payments.created_at >= NOW() - INTERVAL '1 day'
-- GROUP BY users.name
-- HAVING count(payments.id) >= 3
-- ORDER BY failed_count DESC

-- В течении суток на любую дату
SELECT users.name, COUNT(payments.id) AS failed_count
FROM payments
         INNER JOIN orders ON payments.order_id = orders.id
         INNER JOIN users ON orders.user_id = users.id
WHERE payments.status = 'failed'
GROUP BY users.name, DATE(payments.created_at)
HAVING COUNT(payments.id) >= 3
ORDER BY failed_count DESC;