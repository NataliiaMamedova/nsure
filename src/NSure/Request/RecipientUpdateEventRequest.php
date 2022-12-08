<?php

declare(strict_types=1);

namespace App\NSure\Request;

use App\VO\ClientRequestId;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\EventId;
use App\VO\FirstName;
use App\VO\IP;
use App\VO\LastName;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserAgent;
use App\VO\UserId;
use JsonException;

class RecipientUpdateEventRequest extends AbstractNSureRequest
{
    use SessionInfoTrait;

    public function __construct(
        UserId $userId,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId,
        Timestamp $timestamp,
        EventId $eventId,
        ClientRequestId $clientRequestId,
        private Email $email,
        private FirstName $firstName,
        private LastName $lastName,
        private PhoneNumber $phoneNumber,
        private PhoneCountryCode $phoneCountryCode,
        private ?TransactionId $transactionId,
    ) {
        parent::__construct($eventId, $timestamp, $userId, $clientRequestId);
        $this->deviceId = $deviceId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
    }

    /**
     * @throws JsonException
     */
    public function makeBody(): string
    {
        $personalInfo = [
            'personalInfo' => [
                'email' => $this->email->getValue(),
                'firstName' => $this->firstName->getValue(),
                'lastName' => $this->lastName->getValue(),
                'phoneInfo' => [
                    'phone' => $this->phoneNumber->getValue(),
                    'countryCode' => $this->phoneCountryCode->getValue(),
                ],
            ],
        ];

        $requestData = array_merge($this->getMetaData(), $this->getSessionInfo(), $personalInfo);

        if (null !== $this->transactionId) {
            $requestData['txId'] = (string) $this->transactionId->getValue();
        }

        return json_encode($requestData, JSON_THROW_ON_ERROR);
    }
}
