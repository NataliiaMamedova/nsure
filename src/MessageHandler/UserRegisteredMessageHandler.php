<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Exception\UserEventServiceException;
use App\Message\UserRegisteredMessage;
use App\Service\UserEventService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UserRegisteredMessageHandler implements MessageHandlerInterface
{
    private UserEventService $service;

    public function __construct(UserEventService $userEventService)
    {
        $this->service = $userEventService;
    }

    /**
     * @throws UserEventServiceException
     */
    public function __invoke(UserRegisteredMessage $message): void
    {
        $this->service->processUserRegisteredMessage(
            $message->getUserId(),
            $message->getTimestamp(),
            $message->getDeviceId(),
            $message->getUserAgent(),
            $message->getIp(),
            $message->getPhoneNumber(),
            $message->getCountryCode(),
            $message->getEmail()
        );
    }
}
