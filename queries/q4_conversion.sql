SELECT count(id) FROM payments
WHERE payments.created_at >= '2026-01-01'
AND payments.status = 'paid'