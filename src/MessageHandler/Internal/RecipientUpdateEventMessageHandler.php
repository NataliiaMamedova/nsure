<?php

declare(strict_types=1);

namespace App\MessageHandler\Internal;

use App\Exception\NSureServiceException;
use App\Message\Internal\RecipientUpdateEventMessage;
use App\NSure\Service\NSureService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RecipientUpdateEventMessageHandler implements MessageHandlerInterface
{
    private NSureService $nSureService;

    public function __construct(NSureService $nSureService)
    {
        $this->nSureService = $nSureService;
    }

    /**
     * @throws NSureServiceException
     * @throws \JsonException
     */
    public function __invoke(RecipientUpdateEventMessage $message): void
    {
        $this->nSureService->processRecipientUpdateEvent(
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
            $message->getTransactionId(),
        );
    }
}
