<?php

declare(strict_types=1);

namespace Tests\Unit\Integration\Bitrix24;

use App\Config\Bitrix24Config;
use App\Integration\Bitrix24\Bitrix24Connector;
use App\Integration\Bitrix24\Bitrix24RateLimiter;
use App\Integration\Bitrix24\Exception\Bitrix24ApiException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Spiral\Cache\Storage\ArrayStorage;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Tests\Support\InMemoryLock;

final class Bitrix24ConnectorTest extends TestCase
{
    public function testListDealsByIdSendsCrmDealListFilter(): void
    {
        $mockResponse = new MockResponse(
            \json_encode([
                'result' => [
                    ['ID' => '754727', 'TITLE' => 'Test deal'],
                ],
                'total' => 1,
            ], JSON_THROW_ON_ERROR),
            [
                'http_code' => 200,
                'response_headers' => ['Content-Type: application/json'],
            ],
        );
        $httpClient = new MockHttpClient($mockResponse);

        $connector = $this->createConnector($httpClient);
        $deals = $connector->listDealsById(754727);

        $this->assertSame('POST', $mockResponse->getRequestMethod());
        $this->assertSame(
            'https://example.bitrix24.ru/rest/1/test-webhook/crm.deal.list',
            $mockResponse->getRequestUrl(),
        );
        $this->assertSame(
            ['filter' => ['ID' => 754727]],
            \json_decode((string) $mockResponse->getRequestOptions()['body'], true, flags: JSON_THROW_ON_ERROR),
        );
        $this->assertSame('754727', $deals[0]['ID']);
    }

    public function testListDealsByIdRejectsNonPositiveId(): void
    {
        $httpClient = new MockHttpClient();
        $connector = $this->createConnector($httpClient);

        $this->expectException(\InvalidArgumentException::class);
        $connector->listDealsById(0);
    }

    public function testListDealsByIdThrowsOnBitrixError(): void
    {
        $httpClient = new MockHttpClient(new MockResponse(
            \json_encode([
                'error' => 'ACCESS_DENIED',
                'error_description' => 'REST is available only to authorized users',
            ], JSON_THROW_ON_ERROR),
            ['http_code' => 401],
        ));
        $connector = $this->createConnector($httpClient);

        $this->expectException(Bitrix24ApiException::class);
        $connector->listDealsById(754727);
    }

    public function testListDealsByIdAcquiresRateLimitBeforeRequest(): void
    {
        $cache = new ArrayStorage();
        $calls = 0;
        $httpClient = new MockHttpClient(function () use (&$calls): MockResponse {
            ++$calls;

            return new MockResponse(
                \json_encode(['result' => []], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            );
        });

        $connector = new Bitrix24Connector(
            config: new Bitrix24Config([
                'webhook_url' => 'https://example.bitrix24.ru/rest/1/test-webhook',
                'timeout' => 5,
            ]),
            rateLimiter: new Bitrix24RateLimiter($cache, new InMemoryLock()),
            httpClient: $httpClient,
            logger: new NullLogger(),
        );

        $connector->listDealsById(754727);

        $this->assertSame(1, $calls);
        $this->assertIsArray($cache->get('timestamps'));
    }

    private function createConnector(MockHttpClient $httpClient): Bitrix24Connector
    {
        return new Bitrix24Connector(
            config: new Bitrix24Config([
                'webhook_url' => 'https://example.bitrix24.ru/rest/1/test-webhook',
                'timeout' => 5,
            ]),
            rateLimiter: new Bitrix24RateLimiter(new ArrayStorage(), new InMemoryLock()),
            httpClient: $httpClient,
            logger: new NullLogger(),
        );
    }
}
