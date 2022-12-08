<?php

declare(strict_types=1);

namespace App\DTO;

use App\Trait\MetaDataTrait;
use App\Trait\SessionInfoTrait;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;

class PhoneVerifiedEventDTO
{
    use SessionInfoTrait;

    use MetaDataTrait;

    private PhoneNumber $phoneNumber;

    private PhoneCountryCode $phoneCountryCode;

    public function __construct(
        UserId $userId,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode
    ) {
        $this->userId = $userId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->deviceId = $deviceId;
        $this->timestamp = $timestamp;
        $this->phoneNumber = $phoneNumber;
        $this->phoneCountryCode = $phoneCountryCode;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getPhoneCountryCode(): PhoneCountryCode
    {
        return $this->phoneCountryCode;
    }
}
