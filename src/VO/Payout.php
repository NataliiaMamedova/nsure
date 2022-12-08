<?php

declare(strict_types=1);

namespace App\VO;

class Payout
{
    public function __construct(
        private Amount $amount,
        private Currency $currency,
        private ExchangeRate $exchangeRate,
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

    public function getExchangeRate(): ExchangeRate
    {
        return $this->exchangeRate;
    }
}
