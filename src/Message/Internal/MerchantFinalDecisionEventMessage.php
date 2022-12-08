<?php

declare(strict_types=1);

namespace App\Message\Internal;

use App\Message\AbstractMessage;
use App\Trait\MetaDataTrait;
use App\VO\Decision;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;

class MerchantFinalDecisionEventMessage extends AbstractMessage implements HydratorInterface
{
    use MetaDataTrait;

    private TransactionId $transactionId;

    private Decision $decision;

    /**
     * @var array<string, string>
     */
    private array $gatewayData;

    public function toMessage(array $payload, int $version): self
    {
        return self::create(
            Decision::fromString((string) $payload['decision']),
            TransactionId::fromInt((int) $payload['transaction_id']),
            UserId::fromInt((int) $payload['user_id']),
            Timestamp::fromInt((int) $payload['timestamp']),
            (array) ($payload['gateway_data'] ?? []),
        );
    }

    /**
     * @return static
     */
    public static function create(
        Decision $decision,
        TransactionId $transactionId,
        UserId $userId,
        Timestamp $timestamp,
        array $gatewayData = [],
    ): self {
        $message = new static();
        $message->decision = $decision;
        $message->transactionId = $transactionId;
        $message->userId = $userId;
        $message->timestamp = $timestamp;
        $message->gatewayData = $gatewayData;

        return $message;
    }

    public function supportsHydrate(string $identifier, int $version): bool
    {
        return $identifier === $this->getIdentifier() && $version === $this->getVersion();
    }

    public function getIdentifier(): string
    {
        return 'merchant-final-decision-event';
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getTransactionId(): TransactionId
    {
        return $this->transactionId;
    }

    public function getDecision(): Decision
    {
        return $this->decision;
    }

    public function getGatewayData(): array
    {
        return $this->gatewayData;
    }
}
