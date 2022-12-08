<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SessionInfo;
use App\Exception\SessionInfoNotFoundException;
use App\Exception\SessionInfoRepositoryException;
use App\VO\UserId;

interface SessionInfoRepositoryInterface
{
    /**
     * @throws SessionInfoNotFoundException
     * @throws SessionInfoRepositoryException
     */
    public function getByUserId(UserId $userId): SessionInfo;

    /**
     * @throws SessionInfoRepositoryException
     */
    public function save(SessionInfo $info): void;
}
