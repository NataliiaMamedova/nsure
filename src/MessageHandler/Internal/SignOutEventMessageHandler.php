<?php

declare(strict_types=1);

namespace App\MessageHandler\Internal;

use App\Exception\NSureServiceException;
use App\Message\Internal\SignOutEventMessage;
use App\NSure\Service\NSureService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SignOutEventMessageHandler implements MessageHandlerInterface
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
    public function __invoke(SignOutEventMessage $message): void
    {
        $this->nSureService->processSignOutEvent(
            $message->getTimestamp(),
            $message->getUserId(),
            $message->getDeviceId(),
            $message->getUserAgent(),
            $message->getIp()
        );
    }
}
