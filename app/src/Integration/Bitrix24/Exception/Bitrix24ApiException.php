<?php

declare(strict_types=1);

namespace App\Integration\Bitrix24\Exception;

final class Bitrix24ApiException extends Bitrix24Exception
{
    public function __construct(
        public readonly string $errorCode,
        string $description,
        public readonly int $httpStatus = 0,
    ) {
        parent::__construct($description);
    }
}
