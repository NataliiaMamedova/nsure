<?php

declare(strict_types=1);

namespace App\VO;

class Decision extends AbstractEnumString
{
    private const ACCEPTED = 'Accepted';

    private const REJECTED = 'Rejected';

    private const PROCESSOR_AUTHORIZATION_FAILURE = 'ProcessorAuthorizationFailure';

    private const NSURE_ACCEPTED = 'Accepted';

    private const NSURE_REJECTED = 'Rejected';

    private const NSURE_PROCESSOR_AUTHORIZATION_FAILURE = 'ProcessorAuthorizationFailure';

    private const MAP_INTERNAL_DECISION_TO_NSURE = [
        self::ACCEPTED => self::NSURE_ACCEPTED,
        self::REJECTED => self::NSURE_REJECTED,
        self::PROCESSOR_AUTHORIZATION_FAILURE => self::NSURE_PROCESSOR_AUTHORIZATION_FAILURE,
    ];

    public function getNsureDecision(): string
    {
        return self::MAP_INTERNAL_DECISION_TO_NSURE[$this->getValue()];
    }

    public function getAllowedValues(): array
    {
        return [
            self::ACCEPTED,
            self::REJECTED,
            self::PROCESSOR_AUTHORIZATION_FAILURE,
        ];
    }
}
