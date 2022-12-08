<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler\Internal;

use App\Message\Internal\SignUpEventMessage;
use App\MessageHandler\Internal\SignUpEventMessageHandler;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\IP;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class SignUpEventMessageHandlerTest extends KernelTestCase
{
    private const TIMESTAMP = 1640693579;

    private const USER_ID = 810;

    private const DEVICE_ID = '136016b3-e0bc-4c85-a453-545b112c5986';

    private const PHONE_NUMBER = '5211178455';

    private const COUNTRY_CODE = '44';

    private const EMAIL = 'c.kent@example.com';

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testMerchantFinalDecisionRequestSuccess(): void
    {
        /** @var Client $client */
        $client = self::getContainer()->get(Client::class);
        $client->addResponse(new Response(200, [], '{"ok": true}'));

        $messageHandler = self::getContainer()->get(SignUpEventMessageHandler::class);
        $messageHandler($this->createMessage());

        $lastRequest = $client->getLastRequest();

        self::assertNotFalse($lastRequest, 'there\'s no sent request');

        $requestBody = $lastRequest->getBody();
        $requestBody->rewind();

        self::assertSame('/events', $lastRequest->getUri()->getPath());
        $requestData = \json_decode($requestBody->getContents(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame((string) self::USER_ID, $requestData['metadata']['clientUserId']);
        self::assertSame(self::PHONE_NUMBER, $requestData['personalInfo']['phoneInfo']['phone']);
        self::assertSame(self::COUNTRY_CODE, $requestData['personalInfo']['phoneInfo']['countryCode']);
        self::assertSame(self::EMAIL, $requestData['personalInfo']['email']);
        self::assertSame(self::TIMESTAMP, $requestData['metadata']['timestamp']);
        self::assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15', $requestData['sessionInfo']['userAgent']);
        self::assertSame(self::DEVICE_ID, $requestData['sessionInfo']['deviceId']);
        self::assertSame('signUp', $requestData['metadata']['type']);
        self::assertSame('192.168.10.55', $requestData['sessionInfo']['ip']);
        self::assertSame(self::EMAIL, $requestData['accountInfo']['username']);
        self::assertTrue($requestData['accountInfo']['emailValidated']);
    }

    private function createMessage(): SignUpEventMessage
    {
        return SignUpEventMessage::create(
            UserId::fromInt(self::USER_ID),
            Email::fromString(self::EMAIL),
            IP::fromString('192.168.10.55'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            DeviceId::fromString(self::DEVICE_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            PhoneNumber::fromString(self::PHONE_NUMBER),
            PhoneCountryCode::fromString(self::COUNTRY_CODE)
        );
    }
}
