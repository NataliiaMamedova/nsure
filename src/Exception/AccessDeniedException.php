<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class AccessDeniedException extends AbstractException
{
    public function __construct(string $message, string $errorCode = 'ACCESS_DENIED', ?\Throwable $previous = null)
    {
        parent::__construct($message, $errorCode, $previous, Response::HTTP_FORBIDDEN);
    }

    public static function forTransaction(): self
    {
        return new self('You do not have access to this transaction');
    }
}
