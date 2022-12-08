<?php

declare(strict_types=1);

namespace App\VO;

use App\Exception\ValidationException;
use Paybis\Common\ValueObject\VO\AbstractString;

abstract class AbstractEnumString extends AbstractString
{
    protected function __construct(string $value)
    {
        if (! \in_array($value, $this->getAllowedValues(), true)) {
            throw new ValidationException('not allowed value: ' . $value . '; allowed: ' . \implode(', ', $this->getAllowedValues()));
        }
        parent::__construct($value);
    }

    abstract public function getAllowedValues(): array;
}
