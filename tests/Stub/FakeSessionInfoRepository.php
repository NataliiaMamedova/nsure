<?php

declare(strict_types=1);

namespace App\Tests\Stub;

use App\Entity\SessionInfo;
use App\Exception\SessionInfoNotFoundException;
use App\Repository\SessionInfoRepositoryInterface;
use App\VO\UserId;

class FakeSessionInfoRepository implements SessionInfoRepositoryInterface
{
    private array $sessionInfo = [];

    public function getByUserId(UserId $userId): SessionInfo
    {
        if (! isset($this->sessionInfo[$userId->getValue()])) {
            throw new SessionInfoNotFoundException();
        }

        return $this->sessionInfo[$userId->getValue()];
    }

    public function save(SessionInfo $info): void
    {
        $this->sessionInfo[$info->getUserId()->getValue()] = $info;
    }

    public function getAll(): array
    {
        return $this->sessionInfo;
    }

    public function count(): int
    {
        return count($this->sessionInfo);
    }
}
