<?php

declare(strict_types=1);

namespace App\NSure\Request;

use App\VO\CancelReason;
use App\VO\ClientRequestId;
use App\VO\EventId;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserId;

class TxCancelEventRequest extends AbstractNSureRequest
{
    public function __construct(
        EventId $eventId,
        Timestamp $timestamp,
        UserId $userId,
        ClientRequestId $clientRequestId,
        private CancelReason $cancelReason,
        private TransactionId $transactionId,
    ) {
        parent::__construct($eventId, $timestamp, $userId, $clientRequestId);
    }

    public function makeBody(): string
    {
        $cancelInfo = [
            'cancelInfo' => [
                'reason' => $this->cancelReason->getNsureValue(),
                'txId' => (string) $this->transactionId->getValue(),
            ],
        ];

        return \json_encode(array_merge($this->getMetaData(), $cancelInfo), JSON_THROW_ON_ERROR);
    }
}
