<?php

declare(strict_types=1);

namespace App\Integration\Bitrix24;

use App\Config\Bitrix24Config;
use App\Integration\Bitrix24\Exception\Bitrix24ApiException;
use App\Integration\Bitrix24\Exception\Bitrix24TransportException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Bitrix24Connector
{
    public function __construct(
        private readonly Bitrix24Config $config,
        private readonly Bitrix24RateLimiter $rateLimiter,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Возвращает сделки через crm.deal.list с фильтром по ID.
     *
     * @return list<array<string, mixed>>
     */
    public function listDealsById(int $dealId): array
    {
        if ($dealId <= 0) {
            throw new \InvalidArgumentException('Deal ID must be a positive integer');
        }

        $payload = $this->call('crm.deal.list', [
            'filter' => [
                'ID' => $dealId,
            ],
        ]);

        $result = $payload['result'] ?? [];
        if (!\is_array($result)) {
            throw new Bitrix24ApiException('INVALID_RESPONSE', 'crm.deal.list returned a non-array result');
        }

        /** @var list<array<string, mixed>> $deals */
        $deals = \array_values($result);

        return $deals;
    }

    /**
     * Выполняет REST-метод Bitrix24 с учётом rate limit.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function call(string $method, array $params): array
    {
        $this->rateLimiter->acquire();

        $url = $this->config->getWebhookUrl() . $method;

        $this->logger->info('bitrix24 api request', [
            'method' => $method,
        ]);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'json' => $params,
                'timeout' => $this->config->getTimeout(),
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
            $statusCode = $response->getStatusCode();
            $decoded = $response->toArray(false);
        } catch (DecodingExceptionInterface $exception) {
            throw new Bitrix24TransportException('Bitrix24 returned a non-JSON response', previous: $exception);
        } catch (TransportExceptionInterface $exception) {
            throw new Bitrix24TransportException('Failed to reach Bitrix24 API', previous: $exception);
        }

        if (isset($decoded['error'])) {
            throw new Bitrix24ApiException(
                (string) $decoded['error'],
                (string) ($decoded['error_description'] ?? $decoded['error']),
                $statusCode,
            );
        }

        if ($statusCode >= 400) {
            throw new Bitrix24TransportException(\sprintf('Bitrix24 HTTP error %d', $statusCode));
        }

        return $decoded;
    }
}
