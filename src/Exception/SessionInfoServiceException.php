<?php

declare(strict_types=1);

namespace App\Exception;

class SessionInfoServiceException extends \RuntimeException
{
    public function __construct(string $message, \Throwable $previous)
    {
        parent::__construct($message, $previous->getCode(), $previous);
    }

    public static function failedToSave(\Throwable $previous): self
    {
        return new self('failed to save: ' . $previous->getMessage(), $previous);
    }

    public static function failedToGet(\Throwable $previous): self
    {
        return new self('failed to get: ' . $previous->getMessage(), $previous);
    }
}
