<?php

declare(strict_types=1);

namespace App\VO\PaymentMethod;

use App\VO\PaymentMethod\Card\Bin;
use App\VO\PaymentMethod\Card\CardholderName;
use App\VO\PaymentMethod\Card\CountryCode;
use App\VO\PaymentMethod\Card\LastFourDigits;
use App\VO\PaymentMethod\Card\Scheme;

class Card
{
    public function __construct(
        private Bin $bin,
        private LastFourDigits $lastFourDigits,
        private CardholderName $cardholderName,
        private ?CountryCode $countryCode,
        private ?Scheme $scheme,
        private ?bool $isDebit
    ) {
    }

    public function getBin(): Bin
    {
        return $this->bin;
    }

    public function getLastFourDigits(): LastFourDigits
    {
        return $this->lastFourDigits;
    }

    public function getCardholderName(): CardholderName
    {
        return $this->cardholderName;
    }

    public function getCountryCode(): ?CountryCode
    {
        return $this->countryCode;
    }

    public function getScheme(): ?Scheme
    {
        return $this->scheme;
    }

    public function isDebit(): ?bool
    {
        return $this->isDebit;
    }
}
