<?php

declare(strict_types=1);

namespace App\Message\Internal;

use App\Message\AbstractMessage;
use App\Trait\MetaDataTrait;
use App\Trait\SessionInfoTrait;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;

class SignOutEventMessage extends AbstractMessage implements HydratorInterface
{
    use SessionInfoTrait;

    use MetaDataTrait;

    /**
     * @return static
     */
    public static function create(
        UserId $userId,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp
    ): self {
        $message = new static();
        $message->ip = $ip;
        $message->userAgent = $userAgent;
        $message->userId = $userId;
        $message->deviceId = $deviceId;
        $message->timestamp = $timestamp;

        return $message;
    }

    public function toMessage(array $payload, int $version)
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
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
        return 'sign-out-event';
    }
}
