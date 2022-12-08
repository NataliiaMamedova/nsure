<?php

declare(strict_types=1);

namespace App\VO;

use App\Exception\ValidationException;
use Paybis\Common\ValueObject\VO\AbstractString;

abstract class AbstractNumericString extends AbstractString
{
    /**
     * @throws ValidationException
     */
    protected function __construct(string $value)
    {
        if (! is_numeric($value)) {
            throw new ValidationException('Amount value is not numeric');
        }
        parent::__construct($value);
    }
}
