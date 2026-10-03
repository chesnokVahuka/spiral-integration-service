<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Environment\AppEnvironment;
use Spiral\Exceptions\ExceptionHandler;
use Spiral\Exceptions\Renderer\ConsoleRenderer;
use Spiral\Exceptions\Renderer\JsonRenderer;
use Spiral\Exceptions\Reporter\FileReporter;
use Spiral\Exceptions\Reporter\LoggerReporter;
use Spiral\Http\ErrorHandler\PlainRenderer;
use Spiral\Http\ErrorHandler\RendererInterface;
use Spiral\Http\Middleware\ErrorHandlerMiddleware\EnvSuppressErrors;
use Spiral\Http\Middleware\ErrorHandlerMiddleware\SuppressErrorsInterface;

/**
 * The exception handler bootloader is responsible for registering the exception renderers and reporters.
 *
 * @link https://spiral.dev/docs/basics-errors
 */
final class ExceptionHandlerBootloader extends Bootloader
{
    protected const BINDINGS = [
        SuppressErrorsInterface::class => EnvSuppressErrors::class,
        RendererInterface::class => PlainRenderer::class,
    ];

    public function __construct(
        private readonly ExceptionHandler $handler,
    ) {}

    public function init(AbstractKernel $kernel): void
    {
        // Console renderer is used when the application runs in the console.
        $this->handler->addRenderer(new ConsoleRenderer());

        $kernel->running(function (): void {
            // JSON renderer is used in the HTTP context when a JSON response is expected.
            $this->handler->addRenderer(new JsonRenderer());
        });
    }

    public function boot(LoggerReporter $logger, FileReporter $files, AppEnvironment $appEnv): void
    {
        // Log exceptions using the logger component.
        $this->handler->addReporter($logger);

        // Save detailed information about an exception to a file (snapshot).
        if ($appEnv->isLocal()) {
            $this->handler->addReporter($files);
        }
    }
}
