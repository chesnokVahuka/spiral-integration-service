<?php

declare(strict_types=1);

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
     * Handlers. Daily files on the host: var/log/app-YYYY-MM-DD.log (see docker-compose volume).
     * Buggregator socket handler is attached in App\Application\Bootloader\LoggingBootloader.
     */
    'handlers' => [
        'default' => [
            [
                'class' => 'log.rotate',
                'options' => [
                    'filename' => env(
                        'MONOLOG_FILE',
                        directory('root') . 'var/log/app.log',
                    ),
                    'maxFiles' => (int) env('MONOLOG_MAX_FILES', 30),
                    'level' => Logger::DEBUG,
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
