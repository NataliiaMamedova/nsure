<?php

declare(strict_types=1);

namespace App\NSure\Request;

use App\VO\ClientRequestId;
use App\VO\EventId;
use App\VO\Timestamp;
use App\VO\UserId;

abstract class AbstractNSureRequest implements NSureRequestInterface
{
    private EventId $eventId;

    private Timestamp $timestamp;

    private UserId $userId;

    private ClientRequestId $clientRequestId;

    public function __construct(
        EventId $eventId,
        Timestamp $timestamp,
        UserId $userId,
        ClientRequestId $clientRequestId
    ) {
        $this->eventId = $eventId;
        $this->timestamp = $timestamp;
        $this->userId = $userId;
        $this->clientRequestId = $clientRequestId;
    }

    abstract public function makeBody(): string;

    protected function getMetaData(): array
    {
        return [
            'metadata' => [
                'type' => $this->getEventId()->getValue(),
                'timestamp' => $this->getTimestamp()->getValue(),
                'clientUserId' => (string) $this->getUserId()->getValue(),
                'clientRequestId' => $this->getClientRequestId()->getValue(),
            ],
        ];
    }

    protected function getClientRequestId(): ClientRequestId
    {
        return $this->clientRequestId;
    }

    protected function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }

    protected function getEventId(): EventId
    {
        return $this->eventId;
    }

    protected function getUserId(): UserId
    {
        return $this->userId;
    }
}
