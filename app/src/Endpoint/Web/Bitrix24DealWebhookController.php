<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

/**
 * Inbound webhooks from Bitrix24 (outbound webhook / robot HTTP request).
 */
final class Bitrix24DealWebhookController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Deal update event: data[FIELDS][ID] in query string or form body.
     */
    #[Route(
        route: '/webhooks/bitrix24/deals/update',
        name: 'bitrix24.webhook.deals.update',
        methods: 'POST',
    )]
    public function dealUpdated(
        ServerRequestInterface $request,
        ResponseWrapper $response,
    ): ResponseInterface {
        $dealId = $this->resolveDealId($request);
        if ($dealId === null) {
            return $response->json([
                'status' => 'error',
                'message' => 'Missing or invalid deal ID (expected data[FIELDS][ID])',
            ], 400);
        }

        $this->logger->info('deal update webhook received', [
            'dealId' => $dealId,
        ]);

        return $response->json([
            'status' => 'accepted',
            'dealId' => $dealId,
        ]);
    }

    private function resolveDealId(ServerRequestInterface $request): ?int
    {
        return $this->extractDealIdFromSource($request->getQueryParams())
            ?? $this->extractDealIdFromSource($request->getParsedBody());
    }

    /**
     * @param array<string, mixed>|object|null $source
     */
    private function extractDealIdFromSource(mixed $source): ?int
    {
        if (!\is_array($source)) {
            return null;
        }

        $raw = $source['data']['FIELDS']['ID']
            ?? $source['data[FIELDS][ID]']
            ?? null;

        if ($raw === null || $raw === '') {
            return null;
        }

        if (\is_int($raw)) {
            return $raw > 0 ? $raw : null;
        }

        if (\is_string($raw) && \ctype_digit($raw)) {
            $id = (int) $raw;

            return $id > 0 ? $id : null;
        }

        return null;
    }
}
