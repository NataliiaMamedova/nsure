<?php

declare(strict_types=1);

namespace App\Exception;

use App\VO\ValidationError;
use Symfony\Component\HttpFoundation\Response;

class ValidationException extends AbstractException
{
    public const VALIDATION_ERROR_CODE = 'VALIDATION_ERROR';

    public const REQUEST_PARSING_ERROR_CODE = 'REQUEST_PARSING_ERROR';

    public const INVALID_JSON_CODE = 'INVALID_JSON';

    public const INVALID_XML_CODE = 'INVALID_XML';

    /**
     * @var ValidationError[]
     */
    private array $errors;

    /**
     * @param ValidationError[] $errors
     */
    public function __construct(string $message, string $errorCode = self::VALIDATION_ERROR_CODE, array $errors = [])
    {
        parent::__construct($message, $errorCode, null, Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->errors = $errors;
    }

    public function addError(ValidationError $error): void
    {
        $this->errors[] = $error;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
