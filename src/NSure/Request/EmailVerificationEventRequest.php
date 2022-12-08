<?php

declare(strict_types=1);

namespace App\NSure\Request;

use App\VO\ClientRequestId;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\EventId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;

class EmailVerificationEventRequest extends AbstractNSureRequest
{
    use SessionInfoTrait;

    private Email $email;

    public function __construct(
        EventId $eventId,
        Timestamp $timestamp,
        UserId $userId,
        ClientRequestId $clientRequestId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Email $email
    ) {
        parent::__construct($eventId, $timestamp, $userId, $clientRequestId);
        $this->deviceId = $deviceId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->email = $email;
    }

    public function makeBody(): string
    {
        $verificationAttempt = [
            'verificationAttempt' => [
                'email' => $this->email->getValue(),
            ],
        ];

        return json_encode(array_merge($this->getMetaData(), $this->getSessionInfo(), $verificationAttempt), JSON_THROW_ON_ERROR);
    }
}
