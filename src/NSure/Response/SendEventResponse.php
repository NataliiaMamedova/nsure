<?php

declare(strict_types=1);

namespace App\NSure\Response;

class SendEventResponse implements NSureResponseInterface
{
    private bool $ok;

    public function __construct(bool $ok)
    {
        $this->ok = $ok;
    }

    public function isOk(): bool
    {
        return $this->ok;
    }
}
