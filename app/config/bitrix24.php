<?php

declare(strict_types=1);

/**
 * Incoming Bitrix24 webhook base URL, including user id and secret:
 * https://{portal}.bitrix24.ru/rest/{user_id}/{webhook_code}/
 */
return [
    'webhook_url' => env('BITRIX24_WEBHOOK_URL', ''),
    'timeout' => (float) env('BITRIX24_TIMEOUT', 30),
];
