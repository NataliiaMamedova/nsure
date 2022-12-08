<?php

declare(strict_types=1);

namespace App\Exception;

class NSureClientException extends AbstractException
{
    public const CODE_BAD_RESPONSE = 'BAD_RESPONSE';

    public static function badResponse(string $message, int $responseCode): self
    {
        return new self($message, self::CODE_BAD_RESPONSE, null, $responseCode);
    }
}
