<?php

declare(strict_types=1);

namespace Tests\App\Bootloader;

use RoadRunner\Lock\LockInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;
use Tests\Support\InMemoryLock;

final class TestingLockBootloader extends Bootloader
{
    public function boot(BinderInterface $binder): void
    {
        $binder->bindSingleton(LockInterface::class, InMemoryLock::class);
    }
}
