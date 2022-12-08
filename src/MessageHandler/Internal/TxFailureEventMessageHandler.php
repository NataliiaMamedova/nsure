<?php

declare(strict_types=1);

namespace App\MessageHandler\Internal;

use App\Exception\NSureServiceException;
use App\Message\Internal\TxFailureEventMessage;
use App\NSure\Service\NSureService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TxFailureEventMessageHandler implements MessageHandlerInterface
{
    private NSureService $nSureService;

    public function __construct(NSureService $nSureService)
    {
        $this->nSureService = $nSureService;
    }

    /**
     * @throws NSureServiceException
     */
    public function __invoke(TxFailureEventMessage $message): void
    {
        $this->nSureService->processTxFailure(
            $message->getTimestamp(),
            $message->getUserId(),
            $message->getDeviceId(),
            $message->getUserAgent(),
            $message->getIp(),
            $message->getEmail(),
            $message->getFirstName(),
            $message->getLastName(),
            $message->getPhoneNumber(),
            $message->getPhoneCountryCode(),
            $message->getInvoice(),
            $message->getFailureReason(),
        );
    }
}
