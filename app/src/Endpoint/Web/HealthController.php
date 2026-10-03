<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use Psr\Http\Message\ResponseInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

/**
 * Health check endpoint used to verify that the application is alive.
 */
final class HealthController
{
    #[Route(route: '/health', name: 'health', methods: 'GET')]
    public function index(ResponseWrapper $response): ResponseInterface
    {
        return $response->json([
            'status' => 'ok',
            'timestamp' => (new \DateTimeImmutable())->format(\DATE_ATOM),
        ]);
    }
}
