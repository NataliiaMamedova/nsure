<?php

declare(strict_types=1);

namespace App\VO;

class Metadata
{
    public function __construct(
        private ?IP $ip,
        private ?UserAgent $userAgent,
        private ?DeviceId $deviceId,
    ) {
    }

    public function getIp(): ?IP
    {
        return $this->ip;
    }

    public function getUserAgent(): ?UserAgent
    {
        return $this->userAgent;
    }

    public function getDeviceId(): ?DeviceId
    {
        return $this->deviceId;
    }
}
