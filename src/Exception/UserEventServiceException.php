<?php

declare(strict_types=1);

namespace App\Exception;

final class UserEventServiceException extends AbstractException
{
    public static function processError(\Throwable $e): static
    {
        return new self(
            'process error: ' . $e->getMessage(),
            'USER_EVENT_SERVICE_PROCESS_ERROR',
            $e
        );
    }

    public static function sessionInfoError(\Throwable $e): static
    {
        return new self(
            'session info error: ' . $e->getMessage(),
            'USER_EVENT_SESSION_INFO_ERROR',
            $e
        );
    }
}
