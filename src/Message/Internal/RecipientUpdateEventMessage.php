<?php

declare(strict_types=1);

namespace App\Message\Internal;

use App\Message\AbstractMessage;
use App\Trait\MetaDataTrait;
use App\Trait\SessionInfoTrait;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\FirstName;
use App\VO\IP;
use App\VO\LastName;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserAgent;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;

class RecipientUpdateEventMessage extends AbstractMessage implements HydratorInterface
{
    use SessionInfoTrait;

    use MetaDataTrait;

    private Email $email;

    private FirstName $firstName;

    private LastName $lastName;

    private PhoneNumber $phoneNumber;

    private PhoneCountryCode $phoneCountryCode;

    private ?TransactionId $transactionId;

    /**
     * @return static
     */
    public static function create(
        UserId $userId,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp,
        Email $email,
        FirstName $firstName,
        LastName $lastName,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode,
        ?TransactionId $transactionId,
    ): self {
        $message = new static();
        $message->ip = $ip;
        $message->userAgent = $userAgent;
        $message->userId = $userId;
        $message->deviceId = $deviceId;
        $message->timestamp = $timestamp;
        $message->email = $email;
        $message->firstName = $firstName;
        $message->lastName = $lastName;
        $message->phoneNumber = $phoneNumber;
        $message->phoneCountryCode = $phoneCountryCode;
        $message->transactionId = $transactionId;

        return $message;
    }

    public function toMessage(array $payload, int $version): self
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
            IP::fromString($payload['ip']),
            UserAgent::fromString($payload['user_agent']),
            DeviceId::fromString($payload['device_id']),
            Timestamp::fromInt($payload['timestamp']),
            Email::fromString($payload['email']),
            FirstName::fromString($payload['first_name']),
            LastName::fromString($payload['last_name']),
            PhoneNumber::fromString($payload['phone']),
            PhoneCountryCode::fromString($payload['country_code']),
            isset($payload['tx_id']) ? TransactionId::fromInt((int) $payload['tx_id']) : null,
        );
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getFirstName(): FirstName
    {
        return $this->firstName;
    }

    public function getLastName(): LastName
    {
        return $this->lastName;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getPhoneCountryCode(): PhoneCountryCode
    {
        return $this->phoneCountryCode;
    }

    public function getTransactionId(): ?TransactionId
    {
        return $this->transactionId;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getIdentifier(): string
    {
        return 'recipient-update-event';
    }
}
