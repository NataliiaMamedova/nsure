<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler;

use App\Entity\SessionInfo;
use App\Message\UserTokenRefreshedMessage;
use App\MessageHandler\UserTokenRefreshedMessageHandler;
use App\Repository\SessionInfoRepositoryInterface;
use App\Tests\Stub\FakeSessionInfoRepository;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Metadata;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class UserTokenRefreshedMessageHandlerTest extends KernelTestCase
{
    private const USER_ID = 1;

    private const TIMESTAMP = 1640693579;

    private const DEVICE_ID = 'device_1';

    private const USER_AGENT = 'agent_1';

    private const IP = '0.0.0.0';

    private const NEW_TIMESTAMP = 1640693579;

    private const NEW_DEVICE_ID = 'device_2';

    private const NEW_USER_AGENT = 'agent_2';

    private const NEW_IP = '0.0.0.1';

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testCreateSuccess(): void
    {
        /** @var FakeSessionInfoRepository $sessionInfoRepository */
        $sessionInfoRepository = self::getContainer()->get(SessionInfoRepositoryInterface::class);
        self::assertSame(0, $sessionInfoRepository->count());

        /** @var UserTokenRefreshedMessageHandler $handler */
        $handler = self::getContainer()->get(UserTokenRefreshedMessageHandler::class);
        $handler(
            UserTokenRefreshedMessage::create(
                UserId::fromInt(self::USER_ID),
                Timestamp::fromInt(self::TIMESTAMP),
                new Metadata(
                    IP::fromString(self::IP),
                    UserAgent::fromString(self::USER_AGENT),
                    DeviceId::fromString(self::DEVICE_ID),
                )
            )
        );

        $sessionInfo = $sessionInfoRepository->getByUserId(UserId::fromInt(self::USER_ID));

        self::assertSame(self::USER_ID, $sessionInfo->getUserId()->getValue());
        self::assertSame(self::DEVICE_ID, $sessionInfo->getLatestDeviceId()->getValue());
        self::assertSame(self::IP, $sessionInfo->getLatestIp()->getValue());
        self::assertSame(self::USER_AGENT, $sessionInfo->getLatestUserAgent()->getValue());
        self::assertSame(self::TIMESTAMP, $sessionInfo->getUpdatedAt()->getValue());
    }

    public function testUpdateSuccess(): void
    {
        /** @var FakeSessionInfoRepository $sessionInfoRepository */
        $sessionInfoRepository = self::getContainer()->get(SessionInfoRepositoryInterface::class);
        $sessionInfoRepository->save(
            new SessionInfo(
                UserId::fromInt(self::USER_ID),
                Timestamp::fromInt(self::TIMESTAMP),
                DeviceId::fromString(self::DEVICE_ID),
                UserAgent::fromString(self::USER_AGENT),
                IP::fromString(self::IP),
            )
        );
        self::assertSame(1, $sessionInfoRepository->count());

        /** @var UserTokenRefreshedMessageHandler $handler */
        $handler = self::getContainer()->get(UserTokenRefreshedMessageHandler::class);
        $handler(
            UserTokenRefreshedMessage::create(
                UserId::fromInt(self::USER_ID),
                Timestamp::fromInt(self::NEW_TIMESTAMP),
                new Metadata(
                    IP::fromString(self::NEW_IP),
                    UserAgent::fromString(self::NEW_USER_AGENT),
                    DeviceId::fromString(self::NEW_DEVICE_ID),
                )
            )
        );

        self::assertSame(1, $sessionInfoRepository->count());

        $sessionInfo = $sessionInfoRepository->getByUserId(UserId::fromInt(self::USER_ID));

        self::assertSame(self::USER_ID, $sessionInfo->getUserId()->getValue());
        self::assertSame(self::NEW_DEVICE_ID, $sessionInfo->getLatestDeviceId()->getValue());
        self::assertSame(self::NEW_IP, $sessionInfo->getLatestIp()->getValue());
        self::assertSame(self::NEW_USER_AGENT, $sessionInfo->getLatestUserAgent()->getValue());
        self::assertSame(self::NEW_TIMESTAMP, $sessionInfo->getUpdatedAt()->getValue());
    }
}
