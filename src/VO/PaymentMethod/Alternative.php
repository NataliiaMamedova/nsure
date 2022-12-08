<?php

declare(strict_types=1);

namespace App\VO\PaymentMethod;

class Alternative
{
    public function __construct(
        private Alternative\Type $type
    ) {
    }

    public function getType(): Alternative\Type
    {
        return $this->type;
    }
}
