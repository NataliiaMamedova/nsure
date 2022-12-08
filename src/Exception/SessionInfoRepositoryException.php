<?php

declare(strict_types=1);

namespace App\Exception;

class SessionInfoRepositoryException extends AbstractException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 'SESSION_INFO_ERROR', $previous);
    }
}
