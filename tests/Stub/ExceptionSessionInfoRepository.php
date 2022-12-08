<?php

declare(strict_types=1);

namespace App\Tests\Stub;

use App\Entity\SessionInfo;
use App\Repository\SessionInfoRepositoryInterface;
use App\VO\UserId;

class ExceptionSessionInfoRepository implements SessionInfoRepositoryInterface
{
    private array $sessionInfo = [];

    public function getByUserId(UserId $userId): SessionInfo
    {
        throw new \RuntimeException('error when getting');
    }

    public function save(SessionInfo $info): void
    {
        throw new \RuntimeException('error when saving');
    }
}
