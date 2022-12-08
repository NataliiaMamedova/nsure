<?php

declare(strict_types=1);

namespace App\VO;

class CancelReason extends AbstractEnumString
{
    private const FAILED_FULFILLMENT = 'failedFulfillment';

    private const USER_FRAUD = 'userFraud';

    private const OTHER = 'other';

    private const NSURE_FAILED_FULFILLMENT = 'failedFulfillment';

    private const NSURE_USER_FRAUD = 'userFraud';

    private const NSURE_OTHER = 'other';

    private const DEFAULT_NSURE_VALUE = 'other';

    private const MAP_INTERNAL_TO_NSURE = [
        self::FAILED_FULFILLMENT => self::NSURE_FAILED_FULFILLMENT,
        self::USER_FRAUD => self::NSURE_USER_FRAUD,
        self::OTHER => self::NSURE_OTHER,
    ];

    public function getAllowedValues(): array
    {
        return [
            self::FAILED_FULFILLMENT,
            self::USER_FRAUD,
            self::OTHER,
        ];
    }

    public function getNsureValue(): string
    {
        return self::MAP_INTERNAL_TO_NSURE[$this->getValue()] ?? self::DEFAULT_NSURE_VALUE;
    }
}
