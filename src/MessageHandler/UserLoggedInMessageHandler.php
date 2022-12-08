<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Exception\UserEventServiceException;
use App\Message\UserLoggedInMessage;
use App\Service\UserEventService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UserLoggedInMessageHandler implements MessageHandlerInterface
{
    private UserEventService $service;

    public function __construct(UserEventService $userEventService)
    {
        $this->service = $userEventService;
    }

    /**
     * @throws UserEventServiceException
     */
    public function __invoke(UserLoggedInMessage $message): void
    {
        $this->service->processUserLoggedInMessage(
            $message->getUserId(),
            $message->getTimestamp(),
            $message->getDeviceId(),
            $message->getUserAgent(),
            $message->getIp()
        );
    }
}
