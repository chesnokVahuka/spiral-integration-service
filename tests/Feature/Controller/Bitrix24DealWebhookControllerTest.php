<?php

declare(strict_types=1);

namespace Tests\Feature\Controller;

use Tests\TestCase;

final class Bitrix24DealWebhookControllerTest extends TestCase
{
    public function testAcceptsDealIdFromQueryString(): void
    {
        $response = $this->fakeHttp()->post(
            '/webhooks/bitrix24/deals/update?data[FIELDS][ID]=772477',
        );

        $response->assertOk();

        $payload = \json_decode((string) $response->getOriginalResponse()->getBody(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('accepted', $payload['status']);
        $this->assertSame(772477, $payload['dealId']);
    }

    public function testAcceptsDealIdFromFormBody(): void
    {
        $response = $this->fakeHttp()->post(
            uri: '/webhooks/bitrix24/deals/update',
            data: [
                'data' => [
                    'FIELDS' => [
                        'ID' => '772477',
                    ],
                ],
            ],
        );

        $response->assertOk();
        $payload = \json_decode((string) $response->getOriginalResponse()->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(772477, $payload['dealId']);
    }

    public function testReturnsBadRequestWhenDealIdMissing(): void
    {
        $response = $this->fakeHttp()->post('/webhooks/bitrix24/deals/update');

        $response->assertStatus(400);
    }
}
