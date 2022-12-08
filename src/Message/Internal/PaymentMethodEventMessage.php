<?php

declare(strict_types=1);

namespace App\Message\Internal;

use App\Message\AbstractMessage;
use App\VO\Bin;
use App\VO\CardCountryCode;
use App\VO\CardholderName;
use App\VO\CardId;
use App\VO\CardType;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Last4;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentMethodEventMessage extends AbstractMessage implements HydratorInterface
{
    /**
     * @Assert\Valid
     */
    private Last4 $last4;

    /**
     * @Assert\Valid
     */
    private Bin $bin;

    private UserId $userId;

    private CardId $cardId;

    private Timestamp $timestamp;

    private CardholderName $cardholderName;

    private CardType $cardType;

    private bool $isDebit;

    private CardCountryCode $countryCode;

    private IP $ip;

    private UserAgent $userAgent;

    private DeviceId $deviceId;

    /**
     * @return static
     */
    public static function create(
        UserId $userId,
        CardId $cardId,
        Timestamp $timestamp,
        Last4 $last4,
        Bin $bin,
        CardholderName $cardholderName,
        CardType $cardType,
        bool $isDebit,
        CardCountryCode $cardCountryCode,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId
    ): self {
        $message = new static();
        $message->userId = $userId;
        $message->cardId = $cardId;
        $message->timestamp = $timestamp;
        $message->last4 = $last4;
        $message->bin = $bin;
        $message->cardholderName = $cardholderName;
        $message->cardType = $cardType;
        $message->isDebit = $isDebit;
        $message->countryCode = $cardCountryCode;
        $message->ip = $ip;
        $message->userAgent = $userAgent;
        $message->deviceId = $deviceId;

        return $message;
    }

    public function toMessage(array $payload, int $version)
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
            CardId::fromString($payload['card_id']),
            Timestamp::fromInt($payload['timestamp']),
            Last4::fromString($payload['last4']),
            Bin::fromString($payload['bin']),
            CardholderName::fromString($payload['cardholder_name']),
            CardType::fromString($payload['card_type']),
            $payload['is_debit'],
            CardCountryCode::fromString($payload['country_code']),
            IP::fromString($payload['ip']),
            UserAgent::fromString($payload['user_agent']),
            DeviceId::fromString($payload['device_id'])
        );
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getCardId(): CardId
    {
        return $this->cardId;
    }

    public function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }

    public function getLast4(): Last4
    {
        return $this->last4;
    }

    public function getBin(): Bin
    {
        return $this->bin;
    }

    public function getCardholderName(): CardholderName
    {
        return $this->cardholderName;
    }

    public function getCardType(): CardType
    {
        return $this->cardType;
    }

    public function isDebit(): bool
    {
        return $this->isDebit;
    }

    public function getCountryCode(): CardCountryCode
    {
        return $this->countryCode;
    }

    public function getIp(): IP
    {
        return $this->ip;
    }

    public function getUserAgent(): UserAgent
    {
        return $this->userAgent;
    }

    public function getDeviceId(): DeviceId
    {
        return $this->deviceId;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getIdentifier(): string
    {
        return 'payment-method-event';
    }
}
