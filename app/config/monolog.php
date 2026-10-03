<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    /**
     * Default logging channel.
     */
    'default' => env('MONOLOG_DEFAULT_CHANNEL', 'default'),

    /**
     * Global logging level.
     *
     * @see https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
     */
    'globalLevel' => Logger::toMonologLevel(
        env('MONOLOG_DEFAULT_LEVEL', Logger::DEBUG),
    ),

    /**
     * Handlers. By default logs go to stderr (docker logs); Buggregator socket handler
     * is attached in App\Application\Bootloader\LoggingBootloader.
     */
    'handlers' => [
        'default' => [
            [
                'class' => StreamHandler::class,
                'options' => [
                    'stream' => 'php://stderr',
                ],
            ],
        ],
    ],

    /**
     * Processors.
     */
    'processors' => [
        'default' => [
            [
                'class' => PsrLogMessageProcessor::class,
                'options' => [
                    'dateFormat' => 'Y-m-d\TH:i:s.uP',
                ],
            ],
        ],
    ],
];
