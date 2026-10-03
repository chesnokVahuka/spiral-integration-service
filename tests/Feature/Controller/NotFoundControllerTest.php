<?php

declare(strict_types=1);

namespace Tests\Feature\Controller;

use Tests\TestCase;

final class NotFoundControllerTest extends TestCase
{
    public function testUnknownRouteReturns404Page(): void
    {
        $response = $this->fakeHttp()->get('/does-not-exist');

        $response->assertNotFound();

        $body = (string) $response->getOriginalResponse()->getBody();

        $this->assertStringContainsString('404', $body);
        $this->assertStringContainsString('Страница не найдена', $body);
        $this->assertStringContainsString('text/html', $response->getOriginalResponse()->getHeaderLine('Content-Type'));
    }

    public function testHealthRouteIsNotCaughtByFallback(): void
    {
        $this->fakeHttp()->get('/health')->assertOk();
    }
}
