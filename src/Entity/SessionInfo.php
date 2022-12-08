<?php

declare(strict_types=1);

namespace App\Entity;

use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;

class SessionInfo
{
    private Timestamp $updatedAt;

    public function __construct(
        private UserId $userId,
        ?Timestamp $updatedAt = null,
        private ?DeviceId $latestDeviceId = null,
        private ?UserAgent $latestUserAgent = null,
        private ?IP $latestIp = null,
    ) {
        $this->setUpdatedAt($updatedAt);
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getLatestDeviceId(): ?DeviceId
    {
        return $this->latestDeviceId;
    }

    public function setLatestDeviceId(?DeviceId $latestDeviceId): void
    {
        $this->latestDeviceId = $latestDeviceId;
    }

    public function getLatestUserAgent(): ?UserAgent
    {
        return $this->latestUserAgent;
    }

    public function setLatestUserAgent(?UserAgent $latestUserAgent): void
    {
        $this->latestUserAgent = $latestUserAgent;
    }

    public function getLatestIp(): ?IP
    {
        return $this->latestIp;
    }

    public function setLatestIp(?IP $latestIp): void
    {
        $this->latestIp = $latestIp;
    }

    public function getUpdatedAt(): Timestamp
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?Timestamp $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt ?? Timestamp::fromInt(\time());
    }
}
