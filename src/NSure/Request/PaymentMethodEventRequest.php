<?php

declare(strict_types=1);

namespace App\NSure\Request;

use App\VO\Bin;
use App\VO\CardholderName;
use App\VO\CardType;
use App\VO\ClientRequestId;
use App\VO\DeviceId;
use App\VO\EventId;
use App\VO\IP;
use App\VO\Last4;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;

class PaymentMethodEventRequest extends AbstractNSureRequest
{
    use SessionInfoTrait;

    private Bin $bin;

    private CardType $cardType;

    private CardholderName $cardholderName;

    private bool $isDebit;

    private Last4 $last4;

    public function __construct(
        EventId $eventId,
        Timestamp $timestamp,
        UserId $userId,
        ClientRequestId $clientRequestId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Bin $bin,
        CardType $cardType,
        CardholderName $cardholderName,
        bool $isDebit,
        Last4 $last4
    ) {
        parent::__construct($eventId, $timestamp, $userId, $clientRequestId);
        $this->deviceId = $deviceId;
        $this->userAgent = $userAgent;
        $this->ip = $ip;
        $this->bin = $bin;
        $this->cardType = $cardType;
        $this->cardholderName = $cardholderName;
        $this->isDebit = $isDebit;
        $this->last4 = $last4;
    }

    public function makeBody(): string
    {
        $paymentMethodDetails = [
            'paymentMethodDetails' => [
                'creditCard' => [
                    'bin' => $this->bin->getValue(),
                    'cardType' => $this->cardType->getValue(),
                    'cardHolderName' => $this->cardholderName->getValue(),
                    'isDebit' => $this->isDebit,
                    'last4' => $this->last4->getValue(),
                ],
            ],
        ];

        return json_encode(array_merge($this->getMetaData(), $this->getSessionInfo(), $paymentMethodDetails), JSON_THROW_ON_ERROR);
    }
}
