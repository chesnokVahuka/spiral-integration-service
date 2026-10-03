<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Integration\Bitrix24\Bitrix24Connector;
use Tests\TestCase;

final class Bitrix24ConnectorWiringTest extends TestCase
{
    public function testConnectorIsAvailableInContainer(): void
    {
        $connector = $this->getContainer()->get(Bitrix24Connector::class);

        $this->assertInstanceOf(Bitrix24Connector::class, $connector);
    }
}
