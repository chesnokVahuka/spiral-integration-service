# Spiral Integration Service

Монолитное PHP-приложение на [Spiral Framework](https://spiral.dev) + [RoadRunner](https://roadrunner.dev).
Для отладки в compose поднимается [Buggregator](https://docs.buggregator.dev).

## Быстрый старт

```bash
make install   # создаст .env из .env.example и поставит composer-зависимости
make up        # соберёт образ и поднимет app + buggregator
curl http://localhost:8080/health
```

Ответ: `{"status":"ok","timestamp":"..."}`

## Сервисы

| Сервис        | Назначение                                  | Адрес                    |
|---------------|---------------------------------------------|--------------------------|
| `app`         | PHP 8.4 + RoadRunner + Spiral               | http://localhost:8080    |
| `buggregator` | Debug-сервер (dumps, logs, exceptions, SMTP) | http://localhost:8000    |

### Bitrix24

- `POST /webhooks/bitrix24/deals/update` — исходящий вебхук при изменении сделки; ID в `data[FIELDS][ID]` (query или тело `application/x-www-form-urlencoded`).
- `App\Integration\Bitrix24\Bitrix24Connector` — исходящие REST-запросы через входящий вебхук (`BITRIX24_WEBHOOK_URL`), HTTP — Symfony HttpClient. Лимит **2 запроса в секунду** — состояние в RoadRunner KV (`BITRIX24_RATE_LIMIT_STORAGE`, по умолчанию `rr-memory` → плагин `kv` в `.rr.yaml`) и lock-плагин RR. Метод `listDealsById(int $dealId)` вызывает `crm.deal.list` с `filter[ID]`.

## Интеграция с Buggregator

Настраивается через `.env` (хост `buggregator` — имя сервиса в docker-сети):

- `dump()` / `dd()` → `VAR_DUMPER_FORMAT=server`, `VAR_DUMPER_SERVER=buggregator:9912`
- Monolog → файлы на хосте `var/log/app-YYYY-MM-DD.log` (ротация по дням, каталог смонтирован в compose)
- Monolog → `MONOLOG_SOCKET_HOST=buggregator:9913` (см. `LoggingBootloader`); пустое значение отключает
- Исключения (Sentry) → `SENTRY_DSN=http://sentry@buggregator:8000/1`
- SMTP-ловушка → `buggregator:1025`

## Структура

```
app.php                      # точка входа RoadRunner worker
.rr.yaml                     # конфигурация RoadRunner
app/config/                  # конфиги Spiral
app/src/Application/         # Kernel, bootloaders, обработка исключений
app/src/Endpoint/Web/        # HTTP-контроллеры (HealthController)
docker/app/                  # Dockerfile и php.ini
tests/                       # phpunit (spiral/testing)
```

## Команды

`make up | down | restart | logs | logs-buggregator | rr-reset | test`
