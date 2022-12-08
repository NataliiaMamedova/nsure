<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler\Internal;

use App\Message\Internal\RecipientUpdateEventMessage;
use App\MessageHandler\Internal\RecipientUpdateEventMessageHandler;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\FirstName;
use App\VO\IP;
use App\VO\LastName;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserAgent;
use App\VO\UserId;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class RecipientUpdateEventMessageHandlerTest extends KernelTestCase
{
    private const TIMESTAMP = 1640693579;

    private const USER_ID = 810;

    private const DEVICE_ID = '136016b3-e0bc-4c85-a453-545b112c5986';

    private const TRANSACTION_ID = 123;

    private const EVENT_ID = 'recipientUpdate';

    private const PHONE = '1234567';

    private const PHONE_CODE = '062';

    private const USER_AGENT = 'agent';

    private const IP = '0.0.0.0';

    private const FIRST_NAME = 'first';

    private const LAST_NAME = 'last';

    private const EMAIL = 'email@example.com';

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testSignInEventRequestSuccess(): void
    {
        /** @var Client $client */
        $client = self::getContainer()->get(Client::class);
        $client->addResponse(new Response(200, [], '{"ok": true}'));

        /** @var RecipientUpdateEventMessageHandler $messageHandler */
        $messageHandler = self::getContainer()->get(RecipientUpdateEventMessageHandler::class);
        $messageHandler($this->createMessage());

        $lastRequest = $client->getLastRequest();

        self::assertNotFalse($lastRequest, 'there\'s no sent request');

        $requestBody = $lastRequest->getBody();
        $requestBody->rewind();

        self::assertSame('/events', $lastRequest->getUri()->getPath());
        $requestData = \json_decode($requestBody->getContents(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame((string) self::USER_ID, $requestData['metadata']['clientUserId']);
        self::assertSame(self::TIMESTAMP, $requestData['metadata']['timestamp']);
        self::assertSame(self::EVENT_ID, $requestData['metadata']['type']);
        self::assertNotNull($requestData['metadata']['clientRequestId']);
        self::assertSame(self::USER_AGENT, $requestData['sessionInfo']['userAgent']);
        self::assertSame(self::DEVICE_ID, $requestData['sessionInfo']['deviceId']);
        self::assertSame(self::IP, $requestData['sessionInfo']['ip']);
        self::assertSame(self::EMAIL, $requestData['personalInfo']['email']);
        self::assertSame(self::FIRST_NAME, $requestData['personalInfo']['firstName']);
        self::assertSame(self::LAST_NAME, $requestData['personalInfo']['lastName']);
        self::assertSame(self::PHONE, $requestData['personalInfo']['phoneInfo']['phone']);
        self::assertSame(self::PHONE_CODE, $requestData['personalInfo']['phoneInfo']['countryCode']);
        self::assertSame((string) self::TRANSACTION_ID, $requestData['txId']);
    }

    private function createMessage(): RecipientUpdateEventMessage
    {
        return RecipientUpdateEventMessage::create(
            UserId::fromInt(self::USER_ID),
            IP::fromString(self::IP),
            UserAgent::fromString(self::USER_AGENT),
            DeviceId::fromString(self::DEVICE_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            TransactionId::fromInt(self::TRANSACTION_ID),
        );
    }
}
