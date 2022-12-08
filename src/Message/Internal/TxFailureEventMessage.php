<?php

declare(strict_types=1);

namespace App\Message\Internal;

use App\Message\AbstractMessage;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\FailureReason;
use App\VO\FirstName;
use App\VO\Invoice;
use App\VO\IP;
use App\VO\LastName;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Happyr\MessageSerializer\Hydrator\HydratorInterface;

class TxFailureEventMessage extends AbstractMessage implements HydratorInterface
{
    private UserId $userId;

    private Email $email;

    private FirstName $firstName;

    private LastName $lastName;

    private IP $ip;

    private UserAgent $userAgent;

    private DeviceId $deviceId;

    private Timestamp $timestamp;

    private PhoneNumber $phoneNumber;

    private PhoneCountryCode $phoneCountryCode;

    private Invoice $invoice;

    private FailureReason $failureReason;

    /**
     * @return static
     */
    public static function create(
        UserId $userId,
        Email $email,
        FirstName $firstName,
        LastName $lastName,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode,
        Invoice $invoice,
        FailureReason $failureReason
    ): self {
        $message = new static();
        $message->ip = $ip;
        $message->email = $email;
        $message->firstName = $firstName;
        $message->lastName = $lastName;
        $message->userAgent = $userAgent;
        $message->userId = $userId;
        $message->deviceId = $deviceId;
        $message->timestamp = $timestamp;
        $message->phoneNumber = $phoneNumber;
        $message->phoneCountryCode = $phoneCountryCode;
        $message->invoice = $invoice;
        $message->failureReason = $failureReason;

        return $message;
    }

    public function toMessage(array $payload, int $version)
    {
        return static::create(
            UserId::fromInt((int) $payload['user_id']),
            Email::fromString($payload['email']),
            FirstName::fromString($payload['first_name']),
            LastName::fromString($payload['last_name']),
            IP::fromString($payload['ip']),
            UserAgent::fromString($payload['user_agent']),
            DeviceId::fromString($payload['device_id']),
            Timestamp::fromInt($payload['timestamp']),
            PhoneNumber::fromString($payload['phone']),
            PhoneCountryCode::fromString($payload['country_code']),
            Invoice::fromString($payload['invoice']),
            FailureReason::fromString($payload['failure_reason']),
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

    public function getDeviceId(): DeviceId
    {
        return $this->deviceId;
    }

    public function getUserAgent(): UserAgent
    {
        return $this->userAgent;
    }

    public function getIp(): IP
    {
        return $this->ip;
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

    public function getPhoneCountryCode(): PhoneCountryCode
    {
        return $this->phoneCountryCode;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function getFailureReason(): FailureReason
    {
        return $this->failureReason;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getIdentifier(): string
    {
        return 'tx-failure-event';
    }
}
