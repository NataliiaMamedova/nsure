<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\EmailVerifiedEventDTO;
use App\DTO\PhoneVerifiedEventDTO;
use App\DTO\SignInEventDTO;
use App\DTO\SignUpEventDTO;
use App\Entity\SessionInfo;
use App\Exception\SessionInfoServiceException;
use App\Producer\InternalEventProducer;
use App\Service\SessionInfoService;
use App\Service\UserEventService;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\IP;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class UserEventServiceTest extends TestCase
{
    private const TIMESTAMP = 1640693579;

    private const USER_ID = 1;

    private MockObject|SessionInfoService $sessionInfoService;

    private LoggerInterface|MockObject $logger;

    private InternalEventProducer|MockObject $producer;

    private SessionInfo $sessionInfo;

    private UserEventService $service;

    protected function setUp(): void
    {
        $this->sessionInfoService = $this->createMock(SessionInfoService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->producer = $this->createMock(InternalEventProducer::class);
        $this->service = new UserEventService($this->sessionInfoService, $this->producer);
        $this->sessionInfo = new SessionInfo(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            DeviceId::fromString('u123'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh;)'),
            IP::fromString('210.30.218.219'),
        );
    }

    public function testProcessUserLoggedInMessageWithUpdatedSessionInfo(): void
    {
        $metadata['device_id'] = '136016b3-e0bc-4c85-a453-545b112c5986';
        $metadata['ip'] = '210.30.218.319';
        $metadata['user_agent'] = 'Mozilla/5.0 (Macintosh;)';

        $sessionInfoUpdated = $this->sessionInfo;

        $sessionInfoUpdated->setLatestDeviceId(DeviceId::fromString($metadata['device_id']));
        $sessionInfoUpdated->setLatestIp(IP::fromString($metadata['ip']));
        $sessionInfoUpdated->setLatestUserAgent(UserAgent::fromString($metadata['user_agent']));
        $sessionInfoUpdated->setUpdatedAt();

        $this->sessionInfoService
            ->expects($this->once())
            ->method('saveSessionInfo')
            ->willReturnCallback(
                function (
                    UserId $userId,
                    Timestamp $timestamp,
                    DeviceId $deviceId,
                    UserAgent $userAgent,
                    ?IP $ip
                ) use ($metadata): void {
                    $this->assertSame(self::USER_ID, $userId->getValue());
                    $this->assertSame($metadata['device_id'], $deviceId->getValue());
                    $this->assertSame($metadata['ip'], $ip->getValue());
                    $this->assertSame($metadata['user_agent'], $userAgent->getValue());
                }
            )
        ;

        $signInDto = new SignInEventDTO(
            $sessionInfoUpdated->getUserId(),
            IP::fromString($metadata['ip']),
            UserAgent::fromString($metadata['user_agent']),
            DeviceId::fromString($metadata['device_id']),
            Timestamp::fromInt($sessionInfoUpdated->getUpdatedAt()->getValue())
        );

        $this->producer->expects(static::once())->method('publishSignInEvent')->with($signInDto);

        $this->service->processUserLoggedInMessage(
            $signInDto->getUserId(),
            Timestamp::fromInt($sessionInfoUpdated->getUpdatedAt()->getValue()),
            DeviceId::fromString($metadata['device_id']),
            UserAgent::fromString($metadata['user_agent']),
            IP::fromString($metadata['ip'])
        );

        $this->assertSessionInfoResult($sessionInfoUpdated, $signInDto);
    }

    public function testProcessUserLoggedInMessageWithCreatedSessionInfo(): void
    {
        $metadata['device_id'] = '136016b3-e0bc-4c85-a453-545b112c5986';
        $metadata['ip'] = '210.30.218.319';
        $metadata['user_agent'] = 'Mozilla/5.0 (Macintosh;)';

        $sessionInfoCreated = new SessionInfo(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            DeviceId::fromString($metadata['device_id']),
            UserAgent::fromString($metadata['user_agent']),
            IP::fromString($metadata['ip']),
        );

        $signInDto = new SignInEventDTO(
            $sessionInfoCreated->getUserId(),
            IP::fromString($metadata['ip']),
            UserAgent::fromString($metadata['user_agent']),
            DeviceId::fromString($metadata['device_id']),
            Timestamp::fromInt($sessionInfoCreated->getUpdatedAt()->getValue())
        );

        $this->producer->expects(static::once())->method('publishSignInEvent')->with($signInDto);

        $this->service->processUserLoggedInMessage(
            $signInDto->getUserId(),
            Timestamp::fromInt($sessionInfoCreated->getUpdatedAt()->getValue()),
            DeviceId::fromString($metadata['device_id']),
            UserAgent::fromString($metadata['user_agent']),
            IP::fromString($metadata['ip'])
        );

        $this->assertSessionInfoResult($sessionInfoCreated, $signInDto);
    }

    public function testProcessUserLoggedInMessageWithUserEventServiceException(): void
    {
        $this->sessionInfoService->expects(static::once())
            ->method('saveSessionInfo')
            ->with(static::anything())
            ->willThrowException(SessionInfoServiceException::failedToSave(new \RuntimeException('test')))
        ;

        $metadata['device_id'] = '136016b3-e0bc-4c85-a453-545b112c5986';
        $metadata['ip'] = '210.30.218.319';
        $metadata['user_agent'] = 'Mozilla/5.0 (Macintosh;)';

        $this->expectException(SessionInfoServiceException::class);

        $this->service->processUserLoggedInMessage(
            $this->sessionInfo->getUserId(),
            Timestamp::fromInt($this->sessionInfo->getUpdatedAt()->getValue()),
            DeviceId::fromString($metadata['device_id']),
            UserAgent::fromString($metadata['user_agent']),
            IP::fromString($metadata['ip'])
        );
    }

    public function testProcessUserRefreshedMessageSuccess(): void
    {
        $metadata['device_id'] = '136016b3-e0bc-4c85-a453-545b112c5986';
        $metadata['ip'] = '210.30.218.319';
        $metadata['user_agent'] = 'Mozilla/5.0 (Macintosh;)';

        $this->sessionInfoService
            ->expects($this->once())
            ->method('saveSessionInfo')
            ->willReturnCallback(
                function (
                    UserId $userId,
                    Timestamp $timestamp,
                    DeviceId $deviceId,
                    UserAgent $userAgent,
                    ?IP $ip
                ) use ($metadata): void {
                    $this->assertSame(self::USER_ID, $userId->getValue());
                    $this->assertSame(self::TIMESTAMP, $timestamp->getValue());
                    $this->assertSame($metadata['device_id'], $deviceId->getValue());
                    $this->assertSame($metadata['ip'], $ip->getValue());
                    $this->assertSame($metadata['user_agent'], $userAgent->getValue());
                }
            )
        ;

        $this->service->processUserTokenRefreshedMessage(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            DeviceId::fromString($metadata['device_id']),
            UserAgent::fromString($metadata['user_agent']),
            IP::fromString($metadata['ip'])
        );
    }

    public function testProcessUserRefreshedMessageException(): void
    {
        $this->sessionInfoService->expects(static::once())
            ->method('saveSessionInfo')
            ->with(static::anything())
            ->willThrowException(SessionInfoServiceException::failedToSave(new \RuntimeException('test')))
        ;

        $metadata['device_id'] = '136016b3-e0bc-4c85-a453-545b112c5986';
        $metadata['ip'] = '210.30.218.319';
        $metadata['user_agent'] = 'Mozilla/5.0 (Macintosh;)';

        $this->expectException(SessionInfoServiceException::class);

        $this->service->processUserTokenRefreshedMessage(
            $this->sessionInfo->getUserId(),
            Timestamp::fromInt($this->sessionInfo->getUpdatedAt()->getValue()),
            DeviceId::fromString($metadata['device_id']),
            UserAgent::fromString($metadata['user_agent']),
            IP::fromString($metadata['ip'])
        );
    }

    public function testProcessUserRegisteredMessageSuccess(): void
    {
        $metadata['device_id'] = '136016b3-e0bc-4c85-a453-545b112c5986';
        $metadata['ip'] = '210.30.218.319';
        $metadata['user_agent'] = 'Mozilla/5.0 (Macintosh;)';

        $this->sessionInfoService
            ->expects(static::once())
            ->method('saveSessionInfo');

        $signUpDto = new SignUpEventDTO(
            UserId::fromInt(self::USER_ID),
            IP::fromString($metadata['ip']),
            UserAgent::fromString($metadata['user_agent']),
            DeviceId::fromString($metadata['device_id']),
            Timestamp::fromInt(self::TIMESTAMP),
            Email::fromString('test@gmail.com'),
            PhoneNumber::fromString('123456789'),
            PhoneCountryCode::fromString('44')
        );
        $emailVerificationDto = new EmailVerifiedEventDTO(
            UserId::fromInt(self::USER_ID),
            IP::fromString($metadata['ip']),
            UserAgent::fromString($metadata['user_agent']),
            DeviceId::fromString($metadata['device_id']),
            Timestamp::fromInt(self::TIMESTAMP),
            Email::fromString('test@gmail.com'),
        );
        $phoneVerificationDto = new PhoneVerifiedEventDTO(
            UserId::fromInt(self::USER_ID),
            IP::fromString($metadata['ip']),
            UserAgent::fromString($metadata['user_agent']),
            DeviceId::fromString($metadata['device_id']),
            Timestamp::fromInt(self::TIMESTAMP),
            PhoneNumber::fromString('123456789'),
            PhoneCountryCode::fromString('44')
        );

        $this->producer->expects(static::once())->method('publishSignUpEventMessage')->with($signUpDto);
        $this->producer->expects(static::once())->method('publishEmailVerifiedEventMessage')->with($emailVerificationDto);
        $this->producer->expects(static::once())->method('publishPhoneVerifiedEventMessage')->with($phoneVerificationDto);

        $this->service->processUserRegisteredMessage(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            DeviceId::fromString($metadata['device_id']),
            UserAgent::fromString($metadata['user_agent']),
            IP::fromString($metadata['ip']),
            PhoneNumber::fromString('123456789'),
            PhoneCountryCode::fromString('44'),
            Email::fromString('test@gmail.com')
        );
    }

    public function testProcessUserRegisteredMessageException(): void
    {
        $this->sessionInfoService->expects(static::once())
            ->method('saveSessionInfo')
            ->with(static::anything())
            ->willThrowException(SessionInfoServiceException::failedToSave(new \RuntimeException('test')))
        ;

        $metadata['device_id'] = '136016b3-e0bc-4c85-a453-545b112c5986';
        $metadata['ip'] = '210.30.218.319';
        $metadata['user_agent'] = 'Mozilla/5.0 (Macintosh;)';

        $this->expectException(SessionInfoServiceException::class);

        $this->service->processUserRegisteredMessage(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            DeviceId::fromString($metadata['device_id']),
            UserAgent::fromString($metadata['user_agent']),
            IP::fromString($metadata['ip']),
            PhoneNumber::fromString('123456789'),
            PhoneCountryCode::fromString('44'),
            Email::fromString('test@gmail.com')
        );
    }

    private function assertSessionInfoResult(SessionInfo $sessionInfoUpdated, SignInEventDTO $signInEventDTO): void
    {
        static::assertSame($sessionInfoUpdated->getUserId()->getValue(), $signInEventDTO->getUserId()->getValue());
        static::assertSame($sessionInfoUpdated->getLatestIp()->getValue(), $signInEventDTO->getIp()->getValue());
        static::assertSame($sessionInfoUpdated->getLatestDeviceId()->getValue(), $signInEventDTO->getDeviceId()->getValue());
        static::assertSame($sessionInfoUpdated->getLatestUserAgent()->getValue(), $signInEventDTO->getUserAgent()->getValue());
        static::assertSame($sessionInfoUpdated->getUpdatedAt()->getValue(), $signInEventDTO->getTimestamp()->getValue());
    }
}
