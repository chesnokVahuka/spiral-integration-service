<?php

declare(strict_types=1);

namespace App\Config;

use App\Integration\Bitrix24\Exception\Bitrix24Exception;
use Spiral\Core\InjectableConfig;

final class Bitrix24Config extends InjectableConfig
{
    public const CONFIG = 'bitrix24';

    protected array $config = [
        'webhook_url' => '',
        'timeout' => 30.0,
    ];

    /**
     * Базовый URL входящего вебхука Bitrix24 (с завершающим слэшем).
     */
    public function getWebhookUrl(): string
    {
        $url = \rtrim(\trim((string) ($this->config['webhook_url'] ?? '')), '/');
        if ($url === '') {
            throw new Bitrix24Exception('BITRIX24_WEBHOOK_URL is not configured');
        }

        return $url . '/';
    }

    /**
     * Таймаут HTTP-запросов к API, секунды.
     */
    public function getTimeout(): float
    {
        return (float) ($this->config['timeout'] ?? 30.0);
    }
}
