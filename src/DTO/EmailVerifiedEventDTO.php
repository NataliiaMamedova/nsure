<?php

declare(strict_types=1);

namespace App\DTO;

use App\Trait\MetaDataTrait;
use App\Trait\SessionInfoTrait;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;

class EmailVerifiedEventDTO
{
    use SessionInfoTrait;

    use MetaDataTrait;

    private Email $email;

    public function __construct(
        UserId $userId,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp,
        Email $email
    ) {
        $this->userId = $userId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->deviceId = $deviceId;
        $this->timestamp = $timestamp;
        $this->email = $email;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
