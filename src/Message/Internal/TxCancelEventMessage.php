<?php

declare(strict_types=1);

namespace App\Message\Internal;

use App\Message\AbstractMessage;
use App\VO\CancelReason;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;

class TxCancelEventMessage extends AbstractMessage implements HydratorInterface
{
    private UserId $userId;

    private Timestamp $timestamp;

    private TransactionId $transactionId;

    private CancelReason $cancelReason;

    /**
     * @return static
     */
    public static function create(
        UserId $userId,
        Timestamp $timestamp,
        CancelReason $cancelReason,
        TransactionId $transactionId,
    ): self {
        $message = new static();
        $message->userId = $userId;
        $message->timestamp = $timestamp;
        $message->transactionId = $transactionId;
        $message->cancelReason = $cancelReason;

        return $message;
    }

    public function toMessage(array $payload, int $version): self
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
            Timestamp::fromInt($payload['timestamp']),
            CancelReason::fromString($payload['cancel_reason']),
            TransactionId::fromInt($payload['tx_id'])
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

    public function getTransactionId(): TransactionId
    {
        return $this->transactionId;
    }

    public function getCancelReason(): CancelReason
    {
        return $this->cancelReason;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getIdentifier(): string
    {
        return 'tx-cancel-event';
    }
}
