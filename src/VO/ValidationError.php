<?php

declare(strict_types=1);

namespace App\VO;

class ValidationError
{
    private string $message;

    private ?string $code;

    private ?string $propertyPath;

    private mixed $invalidValue;

    public function __construct(string $message, ?string $code, ?string $propertyPath = null, mixed $invalidValue = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->propertyPath = $propertyPath;
        $this->invalidValue = $invalidValue;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }

    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }
}
