<?php

declare(strict_types=1);

namespace App\NSure\Request;

use App\VO\ClientRequestId;
use App\VO\Decision;
use App\VO\EventId;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserId;

class MerchantFinalDecisionEventRequest extends AbstractNSureRequest
{
    private const EVENT_TYPE = 'merchantFinalDecision';

    public function __construct(
        private Decision $decision,
        private TransactionId $transactionId,
        UserId $userId,
        Timestamp $timestamp,
        ClientRequestId $clientRequestId,
        private array $gatewayData = [],
    ) {
        parent::__construct(EventId::fromString(self::EVENT_TYPE), $timestamp, $userId, $clientRequestId);
    }

    public function makeBody(): string
    {
        return json_encode(
            \array_merge(
                $this->getMetaData(),
                [
                    'merchantFinalDecision' => [
                        'decision' => $this->decision->getNsureDecision(),
                        'txId' => (string) $this->transactionId->getValue(),
                        'rawGatewayData' => $this->gatewayData,
                    ],
                ]
            ),
            JSON_THROW_ON_ERROR
        );
    }
}
