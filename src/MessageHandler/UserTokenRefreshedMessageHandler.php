<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UserTokenRefreshedMessage;
use App\Service\UserEventService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UserTokenRefreshedMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private UserEventService $userEventService
    ) {
    }

    public function __invoke(UserTokenRefreshedMessage $message): void
    {
        $this->userEventService->processUserTokenRefreshedMessage(
            $message->getUserId(),
            $message->getTimestamp(),
            $message->getMetadata()->getDeviceId(),
            $message->getMetadata()->getUserAgent(),
            $message->getMetadata()->getIp(),
        );
    }
}
