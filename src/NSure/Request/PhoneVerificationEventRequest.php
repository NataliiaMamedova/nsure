<?php

declare(strict_types=1);

namespace App\NSure\Request;

use App\VO\ClientRequestId;
use App\VO\DeviceId;
use App\VO\EventId;
use App\VO\IP;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;

class PhoneVerificationEventRequest extends AbstractNSureRequest
{
    use SessionInfoTrait;

    private PhoneNumber $phoneNumber;

    private PhoneCountryCode $phoneCountryCode;

    public function __construct(
        EventId $eventId,
        Timestamp $timestamp,
        UserId $userId,
        ClientRequestId $clientRequestId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode
    ) {
        parent::__construct($eventId, $timestamp, $userId, $clientRequestId);
        $this->deviceId = $deviceId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->phoneNumber = $phoneNumber;
        $this->phoneCountryCode = $phoneCountryCode;
    }

    public function makeBody(): string
    {
        $verificationAttempt = [
            'verificationAttempt' => [
                'phone' => $this->phoneNumber->getValue(),
                'countryCode' => $this->phoneCountryCode->getValue(),
            ],
        ];

        return json_encode(array_merge($this->getMetaData(), $this->getSessionInfo(), $verificationAttempt), JSON_THROW_ON_ERROR);
    }
}
