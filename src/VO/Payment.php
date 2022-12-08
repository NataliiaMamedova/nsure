<?php

declare(strict_types=1);

namespace App\VO;

class Payment
{
    public function __construct(
        private Amount $amount,
        private Currency $currency
    ) {
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
