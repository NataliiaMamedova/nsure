<?php

declare(strict_types=1);

namespace App\Exception;

class SessionInfoNotFoundException extends AbstractException
{
    public function __construct()
    {
        parent::__construct('SessionId not found for user', 'SESSION_INFO_NOT_FOUND');
    }
}
