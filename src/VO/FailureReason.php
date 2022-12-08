<?php

declare(strict_types=1);

namespace App\VO;

class FailureReason extends AbstractEnumString
{
    private const MERCHANT_DECLINED = 'merchantDeclined';

    private const PROCESSOR_DECLINED = 'processorDeclined';

    private const APPLICATION_ERROR = 'applicationError';

    private const SUSPECTED_FRAUD = 'suspectedFraud';

    private const NSURE_MERCHANT_DECLINED = 'merchantDeclined';

    private const NSURE_PROCESSOR_DECLINED = 'processorDeclined';

    private const NSURE_APPLICATION_ERROR = 'applicationError';

    private const NSURE_SUSPECTED_FRAUD = 'suspectedFraud';

    private const MAP_INTERNAL_TO_NSURE = [
        self::MERCHANT_DECLINED => self::NSURE_MERCHANT_DECLINED,
        self::PROCESSOR_DECLINED => self::NSURE_PROCESSOR_DECLINED,
        self::APPLICATION_ERROR => self::NSURE_APPLICATION_ERROR,
        self::SUSPECTED_FRAUD => self::NSURE_SUSPECTED_FRAUD,
    ];

    public function getAllowedValues(): array
    {
        return [
            self::MERCHANT_DECLINED,
            self::PROCESSOR_DECLINED,
            self::APPLICATION_ERROR,
            self::SUSPECTED_FRAUD,
        ];
    }

    public function getNsureValue(): string
    {
        return self::MAP_INTERNAL_TO_NSURE[$this->getValue()] ?? self::NSURE_APPLICATION_ERROR;
    }
}
