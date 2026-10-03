<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\SocketHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Monolog\Config\MonologConfig;

/**
 * The bootloader is responsible for configuring the application specific loggers.
 *
 * When MONOLOG_SOCKET_HOST is set (e.g. buggregator:9913), all application logs
 * are additionally streamed to Buggregator.
 *
 * @link https://spiral.dev/docs/basics-logging
 */
final class LoggingBootloader extends Bootloader
{
    public function init(MonologBootloader $monolog, EnvironmentInterface $env): void
    {
        $host = (string) $env->get('MONOLOG_SOCKET_HOST', '');
        if ($host === '') {
            return;
        }

        $socket = new SocketHandler($host, chunkSize: 10);
        $socket->setFormatter(new JsonFormatter(JsonFormatter::BATCH_MODE_NEWLINES));

        // Buggregator being down must never break the application.
        $monolog->addHandler(MonologConfig::DEFAULT_CHANNEL, new WhatFailureGroupHandler([$socket]));
    }
}
