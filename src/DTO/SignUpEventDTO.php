<?php

declare(strict_types=1);

namespace App\DTO;

use App\Trait\MetaDataTrait;
use App\Trait\SessionInfoTrait;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\IP;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;

class SignUpEventDTO
{
    use SessionInfoTrait;

    use MetaDataTrait;

    private Email $email;

    private PhoneNumber $phoneNumber;

    private PhoneCountryCode $phoneCountryCode;

    public function __construct(
        UserId $userId,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp,
        Email $email,
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
        $this->email = $email;
    }

    public function getEmail(): Email
    {
        return $this->email;
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
