<?php

declare(strict_types=1);

namespace App\Message;

use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Metadata;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;

class UserTokenRefreshedMessage extends AbstractMessage implements HydratorInterface
{
    private UserId $userId;

    private Timestamp $timestamp;

    private Metadata $metadata;

    /**
     * @return static
     */
    public static function create(
        UserId $userId,
        Timestamp $timestamp,
        Metadata $metadata
    ): self {
        $message = new static();
        $message->userId = $userId;
        $message->timestamp = $timestamp;
        $message->metadata = $metadata;

        return $message;
    }

    public function toMessage(array $payload, int $version): self
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
            Timestamp::fromInt((int) $payload['timestamp']),
            new Metadata(
                (null !== $payload['metadata']['ip']) ? IP::fromString($payload['metadata']['ip']) : null,
                (null !== $payload['metadata']['user_agent']) ? UserAgent::fromString($payload['metadata']['user_agent']) : null,
                (null !== $payload['metadata']['device_id']) ? DeviceId::fromString($payload['metadata']['device_id']) : null,
            ),
        );
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getIdentifier(): string
    {
        return 'user-token-refreshed';
    }
}
