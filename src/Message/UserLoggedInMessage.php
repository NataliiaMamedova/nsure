<?php

declare(strict_types=1);

namespace App\Message;

use App\Trait\MetaDataTrait;
use App\Trait\SessionInfoTrait;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;

class UserLoggedInMessage extends AbstractMessage implements HydratorInterface
{
    use MetaDataTrait;

    use SessionInfoTrait;

    public function toMessage(array $payload, int $version)
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
            Timestamp::fromInt((int) $payload['timestamp']),
            DeviceId::fromString((string) $payload['metadata']['device_id']),
            UserAgent::fromString((string) $payload['metadata']['user_agent']),
            IP::fromString((string) $payload['metadata']['ip'])
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
        IP $ip
    ): self {
        $message = new static();
        $message->userId = $userId;
        $message->timestamp = $timestamp;
        $message->ip = $ip;
        $message->userAgent = $userAgent;
        $message->deviceId = $deviceId;

        return $message;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getIdentifier(): string
    {
        return 'user-logged-in';
    }
}
