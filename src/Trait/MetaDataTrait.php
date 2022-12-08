<?php

declare(strict_types=1);

namespace App\Trait;

use App\VO\Timestamp;
use App\VO\UserId;

trait MetaDataTrait
{
    private UserId $userId;

    private Timestamp $timestamp;

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }
}
