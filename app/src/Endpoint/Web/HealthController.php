<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

/**
 * Health check endpoint used to verify that the application is alive.
 */
final class HealthController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    #[Route(route: '/health', name: 'health', methods: 'GET')]
    public function index(ResponseWrapper $response): ResponseInterface
    {
        $this->logger->info('Health check requested');

        return $response->json([
            'status' => 'ok',
            'timestamp' => (new \DateTimeImmutable())->format(\DATE_ATOM),
        ]);
    }
}
