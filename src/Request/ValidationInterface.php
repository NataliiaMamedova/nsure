<?php

declare(strict_types=1);

namespace App\Request;

use App\Exception\ValidationException;

interface ValidationInterface
{
    /**
     * @throws ValidationException
     */
    public function validate(): void;
}
