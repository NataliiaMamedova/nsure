<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler\Internal;

use App\Message\Internal\SignOutEventMessage;
use App\MessageHandler\Internal\SignOutEventMessageHandler;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class SignOutEventMessageHandlerTest extends KernelTestCase
{
    private const TIMESTAMP = 1640693579;

    private const USER_ID = 810;

    private const DEVICE_ID = '136016b3-e0bc-4c85-a453-545b112c5986';

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testSignInEventRequestSuccess(): void
    {
        /** @var Client $client */
        $client = self::getContainer()->get(Client::class);
        $client->addResponse(new Response(200, [], '{"ok": true}'));

        $messageHandler = self::getContainer()->get(SignOutEventMessageHandler::class);
        $messageHandler($this->createMessage());

        $lastRequest = $client->getLastRequest();

        self::assertNotFalse($lastRequest, 'there\'s no sent request');

        $requestBody = $lastRequest->getBody();
        $requestBody->rewind();

        self::assertSame('/events', $lastRequest->getUri()->getPath());
        $requestData = \json_decode($requestBody->getContents(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame((string) self::USER_ID, $requestData['metadata']['clientUserId']);
        self::assertSame(self::TIMESTAMP, $requestData['metadata']['timestamp']);
        self::assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15', $requestData['sessionInfo']['userAgent']);
        self::assertSame(self::DEVICE_ID, $requestData['sessionInfo']['deviceId']);
        self::assertSame('signOut', $requestData['metadata']['type']);
        self::assertSame('192.168.10.55', $requestData['sessionInfo']['ip']);
    }

    private function createMessage(): SignOutEventMessage
    {
        return SignOutEventMessage::create(
            UserId::fromInt(self::USER_ID),
            IP::fromString('192.168.10.55'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            DeviceId::fromString(self::DEVICE_ID),
            Timestamp::fromInt(self::TIMESTAMP)
        );
    }
}
