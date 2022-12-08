<?php

declare(strict_types=1);

namespace App\Message\Internal;

use App\Message\AbstractMessage;
use App\Trait\MetaDataTrait;
use App\Trait\SessionInfoTrait;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\IP;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;

class SignUpEventMessage extends AbstractMessage implements HydratorInterface
{
    use SessionInfoTrait;

    use MetaDataTrait;

    private Email $email;

    private PhoneNumber $phoneNumber;

    private PhoneCountryCode $phoneCountryCode;

    /**
     * @return static
     */
    public static function create(
        UserId $userId,
        Email $email,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode
    ): self {
        $message = new static();
        $message->ip = $ip;
        $message->email = $email;
        $message->userAgent = $userAgent;
        $message->userId = $userId;
        $message->deviceId = $deviceId;
        $message->timestamp = $timestamp;
        $message->phoneNumber = $phoneNumber;
        $message->phoneCountryCode = $phoneCountryCode;

        return $message;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function toMessage(array $payload, int $version)
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
            Email::fromString($payload['email']),
            IP::fromString($payload['ip']),
            UserAgent::fromString($payload['user_agent']),
            DeviceId::fromString($payload['device_id']),
            Timestamp::fromInt($payload['timestamp']),
            PhoneNumber::fromString($payload['phone']),
            PhoneCountryCode::fromString($payload['country_code'])
        );
    }

    public function getPhoneCountryCode(): PhoneCountryCode
    {
        return $this->phoneCountryCode;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getIdentifier(): string
    {
        return 'sign-up-event';
    }
}
