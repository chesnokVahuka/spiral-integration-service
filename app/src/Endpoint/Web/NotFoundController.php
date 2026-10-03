<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use Psr\Http\Message\ResponseInterface;
use Spiral\Http\ResponseWrapper;

/**
 * Renders the default 404 page for unmatched HTTP routes.
 */
final class NotFoundController
{
    public function show(ResponseWrapper $response): ResponseInterface
    {
        $html = (string) \file_get_contents(directory('root') . 'app/views/errors/404.html');

        return $response->html($html, 404);
    }
}
