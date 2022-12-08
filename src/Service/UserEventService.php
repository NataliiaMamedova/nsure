<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\EmailVerifiedEventDTO;
use App\DTO\PhoneVerifiedEventDTO;
use App\DTO\SignInEventDTO;
use App\DTO\SignUpEventDTO;
use App\Producer\InternalEventProducer;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\IP;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;

class UserEventService
{
    private SessionInfoService $sessionInfoService;

    private InternalEventProducer $internalEventProducer;

    public function __construct(
        SessionInfoService $sessionInfoService,
        InternalEventProducer $internalEventProducer
    ) {
        $this->sessionInfoService = $sessionInfoService;
        $this->internalEventProducer = $internalEventProducer;
    }

    public function processUserTokenRefreshedMessage(
        UserId $userId,
        Timestamp $timestamp,
        ?DeviceId $deviceId,
        ?UserAgent $userAgent,
        ?IP $ip
    ): void {
        $this->sessionInfoService->saveSessionInfo(
            $userId,
            $timestamp,
            $deviceId,
            $userAgent,
            $ip,
        );
    }

    public function processUserLoggedInMessage(
        UserId $userId,
        Timestamp $timestamp,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip
    ): void {
        $this->sessionInfoService->saveSessionInfo(
            $userId,
            $timestamp,
            $deviceId,
            $userAgent,
            $ip,
        );
        $this->internalEventProducer->publishSignInEvent(new SignInEventDTO($userId, $ip, $userAgent, $deviceId, $timestamp));
    }

    public function processUserRegisteredMessage(
        UserId $userId,
        Timestamp $timestamp,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode,
        Email $email
    ): void {
        $this->sessionInfoService->saveSessionInfo(
            $userId,
            $timestamp,
            $deviceId,
            $userAgent,
            $ip,
        );

        $signUpDto = new SignUpEventDTO(
            $userId,
            $ip,
            $userAgent,
            $deviceId,
            $timestamp,
            $email,
            $phoneNumber,
            $phoneCountryCode
        );
        $emailVerificationDto = new EmailVerifiedEventDTO(
            $userId,
            $ip,
            $userAgent,
            $deviceId,
            $timestamp,
            $email
        );
        $phoneVerificationDto = new PhoneVerifiedEventDTO(
            $userId,
            $ip,
            $userAgent,
            $deviceId,
            $timestamp,
            $phoneNumber,
            $phoneCountryCode
        );

        $this->internalEventProducer->publishSignUpEventMessage($signUpDto);
        $this->internalEventProducer->publishEmailVerifiedEventMessage($emailVerificationDto);
        $this->internalEventProducer->publishPhoneVerifiedEventMessage($phoneVerificationDto);
    }
}
