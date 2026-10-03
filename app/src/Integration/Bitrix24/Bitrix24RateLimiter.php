<?php

declare(strict_types=1);

namespace App\Integration\Bitrix24;

use Psr\SimpleCache\CacheInterface;
use RoadRunner\Lock\LockInterface;

/**
 * Ограничитель частоты запросов (скользящее окно 1 с, не более 2 запросов).
 * Состояние хранится в KV RoadRunner, изменения синхронизируются через lock-плагин RR.
 */
final class Bitrix24RateLimiter
{
    public const MAX_REQUESTS_PER_WINDOW = 2;
    public const WINDOW_SECONDS = 1.0;
    private const CACHE_KEY = 'timestamps';
    private const LOCK_RESOURCE = 'bitrix24.api.rate_limit';
    private const STATE_TTL_SECONDS = 2;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly LockInterface $locker,
    ) {}

    /**
     * Блокирует выполнение, пока слот запроса в текущем окне не освободится.
     */
    public function acquire(): void
    {
        while (true) {
            $lockId = $this->locker->lock(self::LOCK_RESOURCE, waitTTL: 30);
            if ($lockId === false) {
                \usleep(10_000);

                continue;
            }

            try {
                if ($this->tryAcquireSlot()) {
                    return;
                }
            } finally {
                $this->locker->release(self::LOCK_RESOURCE, $lockId);
            }
        }
    }

    /**
     * Пытается зарезервировать слот; при переполнении окна возвращает false (нужно подождать).
     */
    private function tryAcquireSlot(): bool
    {
        while (true) {
            $now = \microtime(true);
            $timestamps = $this->filterTimestamps(
                $this->normalizeTimestamps($this->cache->get(self::CACHE_KEY, [])),
                $now,
            );

            if (\count($timestamps) < self::MAX_REQUESTS_PER_WINDOW) {
                $timestamps[] = $now;
                $this->cache->set(self::CACHE_KEY, $timestamps, self::STATE_TTL_SECONDS);

                return true;
            }

            $wait = self::WINDOW_SECONDS - ($now - $timestamps[0]);
            if ($wait <= 0) {
                continue;
            }

            \usleep((int) \ceil($wait * 1_000_000.0));

            return false;
        }
    }

    /**
     * Приводит значение из кэша к списку меток времени запросов.
     *
     * @return list<float>
     */
    private function normalizeTimestamps(mixed $raw): array
    {
        if (!\is_array($raw)) {
            return [];
        }

        $timestamps = [];
        foreach ($raw as $value) {
            if (\is_int($value) || \is_float($value) || (\is_string($value) && \is_numeric($value))) {
                $timestamps[] = (float) $value;
            }
        }

        return $timestamps;
    }

    /**
     * Оставляет только метки времени, попадающие в текущее окно.
     *
     * @param list<float> $timestamps
     * @return list<float>
     */
    private function filterTimestamps(array $timestamps, float $now): array
    {
        return \array_values(\array_filter(
            $timestamps,
            static fn(float $timestamp): bool => ($now - $timestamp) < self::WINDOW_SECONDS,
        ));
    }
}
