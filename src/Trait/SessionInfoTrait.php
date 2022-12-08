<?php

declare(strict_types=1);

namespace App\Trait;

use App\VO\DeviceId;
use App\VO\IP;
use App\VO\UserAgent;

trait SessionInfoTrait
{
    private DeviceId $deviceId;

    private UserAgent $userAgent;

    private IP $ip;

    public function getDeviceId(): DeviceId
    {
        return $this->deviceId;
    }

    public function getUserAgent(): UserAgent
    {
        return $this->userAgent;
    }

    public function getIp(): IP
    {
        return $this->ip;
    }
}
