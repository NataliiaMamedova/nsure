<?php

declare(strict_types=1);

namespace App\MessageHandler\Internal;

use App\Message\Internal\MerchantFinalDecisionEventMessage;
use App\NSure\Service\NSureService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MerchantFinalDecisionEventMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private NSureService $nSureService,
    ) {
    }

    public function __invoke(MerchantFinalDecisionEventMessage $message): void
    {
        $this->nSureService->processMerchantFinalDecisionEvent(
            $message->getDecision(),
            $message->getTransactionId(),
            $message->getUserId(),
            $message->getTimestamp(),
            $message->getGatewayData(),
        );
    }
}
