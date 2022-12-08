<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends AbstractException
{
    public function __construct(string $message, string $errorCode = 'NOT_FOUND', ?\Throwable $previous = null)
    {
        parent::__construct($message, $errorCode, $previous, Response::HTTP_NOT_FOUND);
    }

    public static function forTransaction(): self
    {
        return new self('Transaction not found');
    }

    public static function forSessionInfo(): self
    {
        return new self('SessionInfo not found');
    }
}
