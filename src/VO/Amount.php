<?php

declare(strict_types=1);

namespace App\VO;

use App\Exception\ValidationException;

class Amount extends AbstractNumericString
{
    private const SCALE = 18;

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

    public function toCents(int $scale = self::SCALE): string
    {
        return bcmul($this->value, '100', $scale);
    }
}
