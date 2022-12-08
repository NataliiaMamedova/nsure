<?php

declare(strict_types=1);

namespace App\Exception;

final class NSureServiceException extends AbstractException
{
    public static function implementationError(\Throwable $e): static
    {
        return new self(
            'implementation error: ' . $e->getMessage(),
            'MERCHANT_FINAL_DECISION_IMPLEMENTATION_ERROR',
            $e
        );
    }

    public static function clientError(\Throwable $e): static
    {
        return new self(
            'client error: ' . $e->getMessage(),
            'MERCHANT_FINAL_DECISION_CLIENT_ERROR',
            $e
        );
    }
}
