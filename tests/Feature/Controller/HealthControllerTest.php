<?php

declare(strict_types=1);

namespace Tests\Feature\Controller;

use Tests\TestCase;

final class HealthControllerTest extends TestCase
{
    public function testHealthCheckReturnsOk(): void
    {
        $response = $this->fakeHttp()->get('/health');

        $response->assertOk();

        $payload = \json_decode((string) $response->getOriginalResponse()->getBody(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('ok', $payload['status']);
        $this->assertArrayHasKey('timestamp', $payload);
    }
}
