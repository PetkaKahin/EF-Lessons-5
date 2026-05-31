EXPLAIN ANALYSE

SELECT
    count(*) AS total_orders,
    count(*) FILTER (WHERE status = 'paid') AS paid_orders,
    round(100.0 * count(*) FILTER (WHERE status = 'paid') / nullif(count(*), 0), 2) AS conversion_percent -- как я понял конверсия - это % от числа paid
FROM orders
WHERE created_at >= :date_from --'2026-01-01'
    AND created_at < :date_to; --'2026-06-01'
