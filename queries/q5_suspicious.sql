SELECT users.name, count(payments.id) as failed_count FROM payments
INNER JOIN orders ON payments.order_id = orders.id
INNER JOIN users ON orders.user_id = users.id
WHERE payments.status = 'failed'
GROUP BY users.name
HAVING COUNT(payments.id) >= 3
ORDER BY failed_count DESC
