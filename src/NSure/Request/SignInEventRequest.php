<?php

declare(strict_types=1);

namespace App\NSure\Request;

use App\VO\ClientRequestId;
use App\VO\DeviceId;
use App\VO\EventId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use JsonException;

class SignInEventRequest extends AbstractNSureRequest
{
    use SessionInfoTrait;

    public function __construct(
        UserId $userId,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp,
        EventId $eventId,
        ClientRequestId $clientRequestId,
    ) {
        parent::__construct($eventId, $timestamp, $userId, $clientRequestId);
        $this->deviceId = $deviceId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
    }

    /**
     * @throws JsonException
     */
    public function makeBody(): string
    {
        return json_encode(array_merge($this->getMetaData(), $this->getSessionInfo()), JSON_THROW_ON_ERROR);
    }
}
