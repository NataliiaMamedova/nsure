<?php

declare(strict_types=1);

namespace App\MessageHandler\Internal;

use App\Exception\NSureServiceException;
use App\Message\Internal\EmailVerificationEventMessage;
use App\NSure\Service\NSureService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EmailVerificationEventMessageHandler implements MessageHandlerInterface
{
    private NSureService $nSureService;

    public function __construct(NSureService $nSureService)
    {
        $this->nSureService = $nSureService;
    }

    /**
     * @throws NSureServiceException|\JsonException
     */
    public function __invoke(EmailVerificationEventMessage $message): void
    {
        $this->nSureService->processEmailVerificationEvent(
            $message->getTimestamp(),
            $message->getUserId(),
            $message->getDeviceId(),
            $message->getUserAgent(),
            $message->getIp(),
            $message->getEmail()
        );
    }
}
