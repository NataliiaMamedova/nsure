<?php

declare(strict_types=1);

namespace App\Message\Internal;

use App\Message\AbstractMessage;
use App\Trait\MetaDataTrait;
use App\Trait\SessionInfoTrait;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;

class PhoneVerificationEventMessage extends AbstractMessage implements HydratorInterface
{
    use SessionInfoTrait;

    use MetaDataTrait;

    private PhoneNumber $phone;

    private PhoneCountryCode $countryCode;

    /**
     * @return static
     */
    public static function create(
        UserId $userId,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp
    ): self {
        $message = new static();
        $message->ip = $ip;
        $message->phone = $phoneNumber;
        $message->userAgent = $userAgent;
        $message->userId = $userId;
        $message->deviceId = $deviceId;
        $message->timestamp = $timestamp;
        $message->countryCode = $phoneCountryCode;

        return $message;
    }

    public function getCountryCode(): PhoneCountryCode
    {
        return $this->countryCode;
    }

    public function getPhone(): PhoneNumber
    {
        return $this->phone;
    }

    public function toMessage(array $payload, int $version)
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
            PhoneNumber::fromString($payload['phone']),
            PhoneCountryCode::fromString($payload['country_code']),
            IP::fromString($payload['ip']),
            UserAgent::fromString($payload['user_agent']),
            DeviceId::fromString($payload['device_id']),
            Timestamp::fromInt($payload['timestamp'])
        );
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getIdentifier(): string
    {
        return 'phone-verification-event';
    }
}
