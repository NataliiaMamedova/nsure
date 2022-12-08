<?php

declare(strict_types=1);

namespace App\Producer;

use App\DTO\EmailVerifiedEventDTO;
use App\DTO\PhoneVerifiedEventDTO;
use App\DTO\SignInEventDTO;
use App\DTO\SignUpEventDTO;
use App\Message\Internal\EmailVerificationEventMessage;
use App\Message\Internal\PhoneVerificationEventMessage;
use App\Message\Internal\SignInEventMessage;
use App\Message\Internal\SignUpEventMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class InternalEventProducer
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function publishSignInEvent(SignInEventDTO $signInEventDTO): void
    {
        $message = SignInEventMessage::create(
            $signInEventDTO->getUserId(),
            $signInEventDTO->getIp(),
            $signInEventDTO->getUserAgent(),
            $signInEventDTO->getDeviceId(),
            $signInEventDTO->getTimestamp(),
        );
        $this->bus->dispatch(new Envelope($message));
    }

    public function publishSignUpEventMessage(SignUpEventDTO $signUpEventMessage): void
    {
        $message = SignUpEventMessage::create(
            $signUpEventMessage->getUserId(),
            $signUpEventMessage->getEmail(),
            $signUpEventMessage->getIp(),
            $signUpEventMessage->getUserAgent(),
            $signUpEventMessage->getDeviceId(),
            $signUpEventMessage->getTimestamp(),
            $signUpEventMessage->getPhoneNumber(),
            $signUpEventMessage->getPhoneCountryCode()
        );

        $this->bus->dispatch(new Envelope($message));
    }

    public function publishEmailVerifiedEventMessage(EmailVerifiedEventDTO $emailVerifiedEventDTO): void
    {
        $message = EmailVerificationEventMessage::create(
            $emailVerifiedEventDTO->getUserId(),
            $emailVerifiedEventDTO->getEmail(),
            $emailVerifiedEventDTO->getIp(),
            $emailVerifiedEventDTO->getUserAgent(),
            $emailVerifiedEventDTO->getDeviceId(),
            $emailVerifiedEventDTO->getTimestamp()
        );

        $this->bus->dispatch(new Envelope($message));
    }

    public function publishPhoneVerifiedEventMessage(PhoneVerifiedEventDTO $phoneVerifiedEventDTO): void
    {
        $message = PhoneVerificationEventMessage::create(
            $phoneVerifiedEventDTO->getUserId(),
            $phoneVerifiedEventDTO->getPhoneNumber(),
            $phoneVerifiedEventDTO->getPhoneCountryCode(),
            $phoneVerifiedEventDTO->getIp(),
            $phoneVerifiedEventDTO->getUserAgent(),
            $phoneVerifiedEventDTO->getDeviceId(),
            $phoneVerifiedEventDTO->getTimestamp()
        );

        $this->bus->dispatch(new Envelope($message));
    }
}
