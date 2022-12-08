<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\SessionInfo;
use App\Exception\NotFoundException;
use App\Exception\SessionInfoServiceException;
use App\Repository\SessionInfoRepositoryInterface;
use App\Service\SessionInfoService;
use App\Tests\Stub\ExceptionSessionInfoRepository;
use App\Tests\Stub\FakeSessionInfoRepository;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ErrorHandler\BufferingLogger;

/**
 * @internal
 */
class SessionInfoServiceTest extends TestCase
{
    private const USER_ID = 1;

    private const USER_ID_2 = 2;

    private const TIMESTAMP = 1640693579;

    private const DEVICE_ID = 'device_1';

    private const USER_AGENT = 'agent_1';

    private const IP = '0.0.0.0';

    private const NEW_TIMESTAMP = 1640693579;

    private const NEW_DEVICE_ID = 'device_2';

    private const NEW_USER_AGENT = 'agent_2';

    private const NEW_IP = '0.0.0.1';

    private ?SessionInfoRepositoryInterface $sessionInfoRepository;

    private ?SessionInfoService $service;

    private ?BufferingLogger $logger;

    protected function setUp(): void
    {
        $this->sessionInfoRepository = new FakeSessionInfoRepository();
        self::assertSame(0, $this->sessionInfoRepository->count());
        $this->logger = new BufferingLogger();
        $this->service = new SessionInfoService($this->sessionInfoRepository, $this->logger);
    }

    protected function tearDown(): void
    {
        $this->service = null;
        $this->logger = null;
        $this->sessionInfoRepository = null;
    }

    public function testNewSuccess(): void
    {
        $this->service->saveSessionInfo(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
        );

        self::assertSame(1, $this->sessionInfoRepository->count());

        $sessionInfo = $this->sessionInfoRepository->getByUserId(UserId::fromInt(self::USER_ID));

        self::assertSame(self::USER_ID, $sessionInfo->getUserId()->getValue());
        self::assertSame(self::DEVICE_ID, $sessionInfo->getLatestDeviceId()->getValue());
        self::assertSame(self::IP, $sessionInfo->getLatestIp()->getValue());
        self::assertSame(self::USER_AGENT, $sessionInfo->getLatestUserAgent()->getValue());
        self::assertSame(self::TIMESTAMP, $sessionInfo->getUpdatedAt()->getValue());
    }

    public function testUpdateSuccess(): void
    {
        $this->sessionInfoRepository->save(
            new SessionInfo(
                UserId::fromInt(self::USER_ID),
                Timestamp::fromInt(self::TIMESTAMP),
                DeviceId::fromString(self::DEVICE_ID),
                UserAgent::fromString(self::USER_AGENT),
                IP::fromString(self::IP),
            )
        );

        self::assertSame(1, $this->sessionInfoRepository->count());

        $this->service->saveSessionInfo(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::NEW_TIMESTAMP),
            DeviceId::fromString(self::NEW_DEVICE_ID),
            UserAgent::fromString(self::NEW_USER_AGENT),
            IP::fromString(self::NEW_IP),
        );

        self::assertSame(1, $this->sessionInfoRepository->count());

        $sessionInfo = $this->sessionInfoRepository->getByUserId(UserId::fromInt(self::USER_ID));

        self::assertSame(self::USER_ID, $sessionInfo->getUserId()->getValue());
        self::assertSame(self::NEW_DEVICE_ID, $sessionInfo->getLatestDeviceId()->getValue());
        self::assertSame(self::NEW_IP, $sessionInfo->getLatestIp()->getValue());
        self::assertSame(self::NEW_USER_AGENT, $sessionInfo->getLatestUserAgent()->getValue());
        self::assertSame(self::NEW_TIMESTAMP, $sessionInfo->getUpdatedAt()->getValue());
    }

    public function testGetSuccessFull(): void
    {
        $this->sessionInfoRepository->save(
            new SessionInfo(
                UserId::fromInt(self::USER_ID),
                Timestamp::fromInt(self::TIMESTAMP),
                DeviceId::fromString(self::DEVICE_ID),
                UserAgent::fromString(self::USER_AGENT),
                IP::fromString(self::IP),
            )
        );

        self::assertSame(1, $this->sessionInfoRepository->count());

        $sessionInfo = $this->service->getByUserId(
            UserId::fromInt(self::USER_ID)
        );

        self::assertSame(1, $this->sessionInfoRepository->count());

        self::assertSame(self::USER_ID, $sessionInfo->getUserId()->getValue());
        self::assertSame(self::DEVICE_ID, $sessionInfo->getLatestDeviceId()->getValue());
        self::assertSame(self::IP, $sessionInfo->getLatestIp()->getValue());
        self::assertSame(self::USER_AGENT, $sessionInfo->getLatestUserAgent()->getValue());
        self::assertSame(self::TIMESTAMP, $sessionInfo->getUpdatedAt()->getValue());
    }

    public function testGetSuccessOnlyUserIdAndTimestamp(): void
    {
        $this->sessionInfoRepository->save(
            new SessionInfo(
                UserId::fromInt(self::USER_ID),
                Timestamp::fromInt(self::TIMESTAMP),
            )
        );

        self::assertSame(1, $this->sessionInfoRepository->count());

        $sessionInfo = $this->service->getByUserId(
            UserId::fromInt(self::USER_ID)
        );

        self::assertSame(1, $this->sessionInfoRepository->count());

        self::assertSame(self::USER_ID, $sessionInfo->getUserId()->getValue());
        self::assertSame(self::TIMESTAMP, $sessionInfo->getUpdatedAt()->getValue());
        self::assertNull($sessionInfo->getLatestDeviceId());
        self::assertNull($sessionInfo->getLatestIp());
        self::assertNull($sessionInfo->getLatestUserAgent());
    }

    public function testGetNotFoundError(): void
    {
        $this->sessionInfoRepository->save(
            new SessionInfo(
                UserId::fromInt(self::USER_ID),
                Timestamp::fromInt(self::TIMESTAMP),
                DeviceId::fromString(self::DEVICE_ID),
                UserAgent::fromString(self::USER_AGENT),
                IP::fromString(self::IP),
            )
        );

        self::assertSame(1, $this->sessionInfoRepository->count());

        $this->expectException(NotFoundException::class);

        $this->service->getByUserId(
            UserId::fromInt(self::USER_ID_2)
        );
    }

    public function testGetWithRuntimeError(): void
    {
        $logger = new BufferingLogger();
        $service = new SessionInfoService(new ExceptionSessionInfoRepository(), $logger);

        $this->expectException(SessionInfoServiceException::class);

        $service->getByUserId(
            UserId::fromInt(self::USER_ID)
        );

        $logs = $logger->cleanLogs();
        self::assertCount(1, $logs);
        self::assertSame('error', $logs[0][0]);
    }

    public function testSaveWithRuntimeError(): void
    {
        $logger = new BufferingLogger();
        $service = new SessionInfoService(new ExceptionSessionInfoRepository(), $logger);

        $this->expectException(SessionInfoServiceException::class);

        $service->saveSessionInfo(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
        );

        $logs = $logger->cleanLogs();
        self::assertCount(1, $logs);
        self::assertSame('error', $logs[0][0]);
    }
}
