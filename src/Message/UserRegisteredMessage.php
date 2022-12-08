<?php

declare(strict_types=1);

namespace App\Message;

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

class UserRegisteredMessage extends AbstractMessage implements HydratorInterface
{
    use MetaDataTrait;

    use SessionInfoTrait;

    private Email $email;

    private PhoneNumber $phoneNumber;

    private PhoneCountryCode $countryCode;

    public function toMessage(array $payload, int $version)
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
            Timestamp::fromInt((int) $payload['timestamp']),
            DeviceId::fromString((string) $payload['metadata']['device_id']),
            UserAgent::fromString((string) $payload['metadata']['user_agent']),
            IP::fromString((string) $payload['metadata']['ip']),
            Email::fromString($payload['email']),
            PhoneNumber::fromString($payload['phone']),
            PhoneCountryCode::fromString($payload['country_code'])
        );
    }

    /**
     * @return static
     */
    public static function create(
        UserId $userId,
        Timestamp $timestamp,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Email $email,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode
    ): self {
        $message = new static();
        $message->userId = $userId;
        $message->timestamp = $timestamp;
        $message->ip = $ip;
        $message->userAgent = $userAgent;
        $message->deviceId = $deviceId;
        $message->email = $email;
        $message->phoneNumber = $phoneNumber;
        $message->countryCode = $phoneCountryCode;

        return $message;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getCountryCode(): PhoneCountryCode
    {
        return $this->countryCode;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getIdentifier(): string
    {
        return 'user-registered';
    }
}
