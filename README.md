## Миграции

```bash
make migrate
```

## Сидер

```bash
make seed
make seed-rollback
```

## Оплата заказа и защита от гонок

```bash
make pay-order ORDER=123   # оплатить заказ с id=123
```

```bash
make race-test
```

## Пагинация

```bash
make offset-pagination
```
```bash
keyset-pagination
```
