<?php

declare(strict_types=1);

namespace Tests\Support;

use RoadRunner\Lock\LockInterface;

/**
 * In-process lock for PHPUnit (RoadRunner lock plugin is not available outside RR workers).
 */
final class InMemoryLock implements LockInterface
{
    /** @var array<string, non-empty-string> */
    private array $locks = [];

    public function lock(
        string $resource,
        ?string $id = null,
        int|float|\DateInterval $ttl = 0,
        int|float|\DateInterval $waitTTL = 0,
    ): false|string {
        $waitUntil = \microtime(true) + $this->seconds($waitTTL);
        $id ??= \bin2hex(\random_bytes(8));

        while (isset($this->locks[$resource])) {
            if ($this->seconds($waitTTL) <= 0 || \microtime(true) >= $waitUntil) {
                return false;
            }

            \usleep(1_000);
        }

        $this->locks[$resource] = $id;

        return $id;
    }

    public function lockRead(
        string $resource,
        ?string $id = null,
        int|float|\DateInterval $ttl = 0,
        int|float|\DateInterval $waitTTL = 0,
    ): false|string {
        return $this->lock($resource, $id, $ttl, $waitTTL);
    }

    public function release(string $resource, string $id): bool
    {
        if (($this->locks[$resource] ?? null) !== $id) {
            return false;
        }

        unset($this->locks[$resource]);

        return true;
    }

    public function forceRelease(string $resource): bool
    {
        unset($this->locks[$resource]);

        return true;
    }

    public function exists(string $resource, ?string $id = null): bool
    {
        if ($id === null) {
            return isset($this->locks[$resource]);
        }

        return ($this->locks[$resource] ?? null) === $id;
    }

    public function updateTTL(string $resource, string $id, int|float|\DateInterval $ttl): bool
    {
        return ($this->locks[$resource] ?? null) === $id;
    }

    private function seconds(int|float|\DateInterval $value): float
    {
        if ($value instanceof \DateInterval) {
            return (float) $value->s + $value->i * 60 + $value->h * 3600;
        }

        return (float) $value;
    }
}
