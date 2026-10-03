<?php

declare(strict_types=1);

use Spiral\Cache\Storage\ArrayStorage;
use Spiral\Cache\Storage\FileStorage;
use Spiral\RoadRunner\KeyValue\StorageInterface;

return [
    'default' => env('CACHE_STORAGE', 'array'),

    'aliases' => [
        'bitrix24-rate-limit' => env('BITRIX24_RATE_LIMIT_STORAGE', 'rr-memory'),
    ],

    'storages' => [
        'array' => [
            'type' => 'array',
        ],
        'file' => [
            'type' => 'file',
            'path' => directory('runtime') . 'cache',
        ],
        'rr-memory' => [
            'type' => 'roadrunner',
            'driver' => 'memory',
        ],
    ],

    'typeAliases' => [
        'array' => ArrayStorage::class,
        'file' => FileStorage::class,
        'roadrunner' => StorageInterface::class,
    ],
];
