SELECT id,
    user_id,
    status,
    total_amount,
    created_at
FROM orders
ORDER BY id DESC
LIMIT :page_size
OFFSET (:page - 1) * :page_size;
