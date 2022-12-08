<?php

declare(strict_types=1);

namespace App\Tests\Unit\NSure\Request;

use App\NSure\Request\RecipientUpdateEventRequest;
use App\VO\ClientRequestId;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\EventId;
use App\VO\FirstName;
use App\VO\IP;
use App\VO\LastName;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserAgent;
use App\VO\UserId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class RecipientUpdateEventRequestTest extends TestCase
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

    private const CLIENT_REQUEST_ID = 'ba4bb3cb-0da5-4885-afe8-85cd12f80b01';

    public function testAllFieldsSuccess(): void
    {
        $jsonContent = $this->getRecipientUpdateEventRequest(TransactionId::fromInt(self::TRANSACTION_ID))->makeBody();

        $body = \json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        $this->assertRequiredFields($body);
        self::assertSame((string) self::TRANSACTION_ID, $body['txId']);
    }

    public function testWithoutTransactionIdSuccess(): void
    {
        $jsonContent = $this->getRecipientUpdateEventRequest()->makeBody();

        $body = \json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        $this->assertRequiredFields($body);
    }

    private function getRecipientUpdateEventRequest(?TransactionId $transactionId = null): RecipientUpdateEventRequest
    {
        return new RecipientUpdateEventRequest(
            UserId::fromInt(self::USER_ID),
            IP::fromString(self::IP),
            UserAgent::fromString(self::USER_AGENT),
            DeviceId::fromString(self::DEVICE_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            EventId::fromString(self::EVENT_ID),
            ClientRequestId::fromString(self::CLIENT_REQUEST_ID),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            $transactionId,
        );
    }

    private function assertRequiredFields(array $body): void
    {
        self::assertSame(self::EVENT_ID, $body['metadata']['type']);
        self::assertSame(self::TIMESTAMP, $body['metadata']['timestamp']);
        self::assertSame((string) self::USER_ID, $body['metadata']['clientUserId']);
        self::assertSame(self::CLIENT_REQUEST_ID, $body['metadata']['clientRequestId']);
        self::assertSame(self::USER_AGENT, $body['sessionInfo']['userAgent']);
        self::assertSame(self::DEVICE_ID, $body['sessionInfo']['deviceId']);
        self::assertSame(self::IP, $body['sessionInfo']['ip']);
        self::assertSame(self::EMAIL, $body['personalInfo']['email']);
        self::assertSame(self::FIRST_NAME, $body['personalInfo']['firstName']);
        self::assertSame(self::LAST_NAME, $body['personalInfo']['lastName']);
        self::assertSame(self::PHONE, $body['personalInfo']['phoneInfo']['phone']);
        self::assertSame(self::PHONE_CODE, $body['personalInfo']['phoneInfo']['countryCode']);
    }
}
