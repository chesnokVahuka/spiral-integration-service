<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Config\Bitrix24Config;
use App\Integration\Bitrix24\Bitrix24Connector;
use App\Integration\Bitrix24\Bitrix24RateLimiter;
use Psr\Log\LoggerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Core\BinderInterface;
use Symfony\Component\HttpClient\HttpClient;

final class Bitrix24Bootloader extends Bootloader
{
    public function boot(BinderInterface $binder): void
    {
        $binder->bindSingleton(
            Bitrix24RateLimiter::class,
            static fn(
                CacheStorageProviderInterface $cacheProvider,
                \RoadRunner\Lock\LockInterface $lock,
            ): Bitrix24RateLimiter => new Bitrix24RateLimiter(
                $cacheProvider->storage('bitrix24-rate-limit'),
                $lock,
            ),
        );

        $binder->bindSingleton(
            Bitrix24Connector::class,
            static fn(
                Bitrix24Config $config,
                Bitrix24RateLimiter $rateLimiter,
                LoggerInterface $logger,
            ): Bitrix24Connector => new Bitrix24Connector(
                config: $config,
                rateLimiter: $rateLimiter,
                httpClient: HttpClient::create([
                    'timeout' => $config->getTimeout(),
                ]),
                logger: $logger,
            ),
        );
    }
}
