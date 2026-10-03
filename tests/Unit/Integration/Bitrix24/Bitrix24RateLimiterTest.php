<?php

declare(strict_types=1);

namespace Tests\Unit\Integration\Bitrix24;

use App\Integration\Bitrix24\Bitrix24RateLimiter;
use Spiral\Cache\Storage\ArrayStorage;
use Tests\Support\InMemoryLock;
use PHPUnit\Framework\TestCase;

final class Bitrix24RateLimiterTest extends TestCase
{
    public function testNeverExceedsTwoRequestsInAnySecond(): void
    {
        $limiter = $this->createLimiter();
        $times = [];

        for ($i = 0; $i < 5; $i++) {
            $limiter->acquire();
            $times[] = \microtime(true);
        }

        foreach ($times as $start) {
            $inWindow = 0;
            foreach ($times as $timestamp) {
                if ($timestamp >= $start && $timestamp < $start + Bitrix24RateLimiter::WINDOW_SECONDS) {
                    ++$inWindow;
                }
            }

            $this->assertLessThanOrEqual(Bitrix24RateLimiter::MAX_REQUESTS_PER_WINDOW, $inWindow);
        }

        $this->assertGreaterThanOrEqual(0.95, $times[2] - $times[0]);
        $this->assertGreaterThanOrEqual(1.95, $times[4] - $times[0]);
    }

    public function testAllowsBurstOfTwoRequestsImmediately(): void
    {
        $limiter = $this->createLimiter();

        $started = \microtime(true);
        $limiter->acquire();
        $limiter->acquire();
        $elapsed = \microtime(true) - $started;

        $this->assertLessThan(0.2, $elapsed);
    }

    private function createLimiter(): Bitrix24RateLimiter
    {
        return new Bitrix24RateLimiter(new ArrayStorage(), new InMemoryLock());
    }
}
