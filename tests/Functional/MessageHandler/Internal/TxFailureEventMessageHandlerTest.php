<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler\Internal;

use App\Message\Internal\TxFailureEventMessage;
use App\MessageHandler\Internal\TxFailureEventMessageHandler;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\FailureReason;
use App\VO\FirstName;
use App\VO\Invoice;
use App\VO\IP;
use App\VO\LastName;
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
class TxFailureEventMessageHandlerTest extends KernelTestCase
{
    private const TRANSACTION_ID = 123;

    private const PAYMENT_AMOUNT = '351.75';

    private const PAYMENT_CURRENCY = 'USD';

    private const PAYOUT_AMOUNT = '3.5';

    private const PAYOUT_CURRENCY = 'BTC';

    private const EXCHANGE_RATE = '100.5';

    private const GATEWAY = 'worldpay';

    private const BIN = '123456';

    private const LAST4 = '6789';

    private const USER_ID = 1;

    private const TIMESTAMP = 1640693579;

    private const CARD_COUNTRY = 'US';

    private const SCHEME = 'visa';

    private const CARDHOLDER_NAME = 'first last';

    private const IS_DEBIT = true;

    private const DEVICE_ID = 'device_1';

    private const EVENT_ID = 'txFailure';

    private const PHONE = '1234567';

    private const PHONE_CODE = '062';

    private const INVOICE = 'invoice';

    private const USER_AGENT = 'agent';

    private const IP = '0.0.0.0';

    private const FIRST_NAME = 'first';

    private const LAST_NAME = 'last';

    private const EMAIL = 'email@example.com';

    private const FAILURE_REASON = 'merchantDeclined';

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testTxFailureEventSuccess(): void
    {
        /** @var Client $client */
        $client = self::getContainer()->get(Client::class);
        // processing api client
        $client->addResponse(new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            '{
    "id": ' . self::TRANSACTION_ID . ',
    "payment":{
        "amount":"' . self::PAYMENT_AMOUNT . '",
        "currency":"' . self::PAYMENT_CURRENCY . '",
        "gateway":"' . self::GATEWAY . '",
        "card":{
            "bin":"' . self::BIN . '",
            "last_four_digits":"' . self::LAST4 . '",
            "country_code":"' . self::CARD_COUNTRY . '",
            "scheme":"' . self::SCHEME . '",
            "cardholder_name":"' . self::CARDHOLDER_NAME . '",
            "is_debit": ' . (self::IS_DEBIT ? 'true' : 'false') . '
        }
    },
    "payout":{
        "currency":"' . self::PAYOUT_CURRENCY . '",
        "amount":"' . self::PAYOUT_AMOUNT . '",
        "exchange_rate":"' . self::EXCHANGE_RATE . '"
    }
}'
        ));

        // nsure client
        $client->addResponse(new Response(200, [], '{"ok": true}'));

        /** @var TxFailureEventMessageHandler $messageHandler */
        $messageHandler = self::getContainer()->get(TxFailureEventMessageHandler::class);
        $messageHandler($this->createMessage());

        $lastRequest = $client->getLastRequest();

        self::assertNotFalse($lastRequest, 'there\'s no sent request');

        $requestBody = $lastRequest->getBody();
        $requestBody->rewind();

        self::assertSame('/events', $lastRequest->getUri()->getPath());
        $requestData = \json_decode($requestBody->getContents(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(self::EVENT_ID, $requestData['metadata']['type']);
        self::assertSame(self::TIMESTAMP, $requestData['metadata']['timestamp']);
        self::assertSame((string) self::USER_ID, $requestData['metadata']['clientUserId']);
        self::assertNotEmpty($requestData['metadata']['clientRequestId']);
        self::assertSame(self::DEVICE_ID, $requestData['sessionInfo']['deviceId']);
        self::assertSame(self::USER_AGENT, $requestData['sessionInfo']['userAgent']);
        self::assertSame(self::IP, $requestData['sessionInfo']['ip']);
        self::assertSame(self::EMAIL, $requestData['personalInfo']['email']);
        self::assertSame(self::FIRST_NAME, $requestData['personalInfo']['firstName']);
        self::assertSame(self::LAST_NAME, $requestData['personalInfo']['lastName']);
        self::assertSame(self::PHONE, $requestData['personalInfo']['phoneInfo']['phone']);
        self::assertSame(self::PHONE_CODE, $requestData['personalInfo']['phoneInfo']['countryCode']);
        self::assertSame(self::EMAIL, $requestData['recipientInfo']['email']);
        self::assertSame(self::FIRST_NAME, $requestData['recipientInfo']['firstName']);
        self::assertSame(self::LAST_NAME, $requestData['recipientInfo']['lastName']);
        self::assertSame(self::PHONE, $requestData['recipientInfo']['phoneInfo']['phone']);
        self::assertSame(self::PHONE_CODE, $requestData['recipientInfo']['phoneInfo']['countryCode']);
        self::assertSame((string) self::TRANSACTION_ID, $requestData['transactionDetails']['txId']);
        self::assertSame((float) self::PAYMENT_AMOUNT, $requestData['transactionDetails']['paidAmount']['valueInCurrency']);
        self::assertSame(self::PAYMENT_CURRENCY, $requestData['transactionDetails']['paidAmount']['currency']);
        self::assertSame(self::GATEWAY, $requestData['transactionDetails']['paymentMethodDetails']['gateway']);
        self::assertSame(self::BIN, $requestData['transactionDetails']['paymentMethodDetails']['creditCard']['bin']);
        self::assertSame(self::LAST4, $requestData['transactionDetails']['paymentMethodDetails']['creditCard']['last4']);
        self::assertSame(self::CARDHOLDER_NAME, $requestData['transactionDetails']['paymentMethodDetails']['creditCard']['cardHolderName']);
        self::assertSame(self::CARD_COUNTRY, $requestData['transactionDetails']['paymentMethodDetails']['creditCard']['countryCode']);
        self::assertSame(self::SCHEME, $requestData['transactionDetails']['paymentMethodDetails']['creditCard']['cardType']);
        self::assertSame(self::IS_DEBIT, $requestData['transactionDetails']['paymentMethodDetails']['creditCard']['isDebit']);
        self::assertSame(self::PAYOUT_CURRENCY, $requestData['transactionDetails']['cart'][0]['brand']);
        self::assertSame((float) self::PAYOUT_AMOUNT, $requestData['transactionDetails']['cart'][0]['quantity']);
        self::assertSame('digital', $requestData['transactionDetails']['cart'][0]['itemFulfillment']);
        self::assertSame((float) self::EXCHANGE_RATE, $requestData['transactionDetails']['cart'][0]['sellingPrice']['valueInCurrency']);
        self::assertSame(self::PAYMENT_CURRENCY, $requestData['transactionDetails']['cart'][0]['sellingPrice']['currency']);
        self::assertSame(self::FAILURE_REASON, $requestData['transactionDetails']['failureReason']);
    }

    private function createMessage(): TxFailureEventMessage
    {
        return TxFailureEventMessage::create(
            UserId::fromInt(self::USER_ID),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            IP::fromString(self::IP),
            UserAgent::fromString(self::USER_AGENT),
            DeviceId::fromString(self::DEVICE_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            Invoice::fromString(self::INVOICE),
            FailureReason::fromString(self::FAILURE_REASON)
        );
    }
}
