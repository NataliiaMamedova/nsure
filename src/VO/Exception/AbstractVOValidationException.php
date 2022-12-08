<?php

declare(strict_types=1);

namespace App\VO\Exception;

use App\Exception\AbstractException;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractVOValidationException extends AbstractException
{
    public function __construct(string $message, string $errorCode, ?\Throwable $previous = null)
    {
        parent::__construct($message, $errorCode, $previous, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
