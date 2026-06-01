SELECT id,
    user_id,
    status,
    total_amount,
    created_at
FROM orders
WHERE id < :cursor_id
ORDER BY id DESC
LIMIT :page_size;
