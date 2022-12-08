<?php

declare(strict_types=1);

namespace App\Tests\Unit\NSure;

use App\Exception\NSureClientException;
use App\NSure\Factory\EventRequestFactory;
use App\NSure\NSureClient;
use App\NSure\Request\EmailVerificationEventRequest;
use App\NSure\Request\MerchantFinalDecisionEventRequest;
use App\NSure\Request\PaymentMethodEventRequest;
use App\NSure\Request\PhoneVerificationEventRequest;
use App\NSure\Request\SignInEventRequest;
use App\NSure\Request\SignOutEventRequest;
use App\NSure\Request\SignUpEventRequest;
use App\NSure\Request\TxFailureEventRequest;
use App\NSure\Response\SendEventResponse;
use App\VO\Amount;
use App\VO\Bin;
use App\VO\CardholderName;
use App\VO\CardType;
use App\VO\ClientRequestId;
use App\VO\Currency;
use App\VO\Decision;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\EventId;
use App\VO\ExchangeRate;
use App\VO\FailureReason;
use App\VO\FirstName;
use App\VO\Gateway;
use App\VO\IP;
use App\VO\Last4;
use App\VO\LastName;
use App\VO\Payment;
use App\VO\PaymentMethod\Card;
use App\VO\Payout;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserAgent;
use App\VO\UserId;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
class NSureClientTest extends TestCase
{
    private const ACCEPTED = 'Accepted';

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

    private const USER_AGENT = 'agent';

    private const IP = '0.0.0.0';

    private const FIRST_NAME = 'first';

    private const LAST_NAME = 'last';

    private const EMAIL = 'email@example.com';

    private const FAILURE_REASON = 'processorDeclined';

    private const CLIENT_REQUEST_ID = 'd7aa909b-d2ba-40ff-8c79-2f898517a7ea';

    private NSureClient $nSureClient;

    /**
     * @var ClientInterface|MockObject
     */
    private ClientInterface $client;

    /**
     * @var RequestFactoryInterface|MockObject
     */
    private RequestFactoryInterface $requestFactory;

    /**
     * @var StreamFactoryInterface|MockObject
     */
    private StreamFactoryInterface $streamFactory;

    /**
     * @var UriFactoryInterface|MockObject
     */
    private UriFactoryInterface $uriFactory;

    /**
     * @var SerializerInterface|MockObject
     */
    private SerializerInterface $serializer;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->client = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->uriFactory = $this->createMock(UriFactoryInterface::class);
        $this->serializer = $this->createMock(Serializer::class);

        $this->nSureClient = new NSureClient(
            $this->client,
            $this->requestFactory,
            $this->streamFactory,
            $this->uriFactory,
            $this->serializer,
            $this->logger
        );
    }

    public function testWrongResponse(): void
    {
        $request = $this->getPhoneVerificationEventRequest();
        $this->requestFactory->method('createRequest')->willReturn(new Request('POST', 'http://something.com'));

        $httpResponse = new Response(200, [], '}');
        $this->client->method('sendRequest')->willReturn($httpResponse);
        try {
            $this->nSureClient->sendEventPhoneVerification($request);
            static::fail();
        } catch (NSureClientException $e) {
            self::assertSame(NSureClientException::CODE_BAD_RESPONSE, $e->getErrorCode());
        }
    }

    public function testWrongRequest(): void
    {
        $request = $this->getPhoneVerificationEventRequest();
        $this->requestFactory->method('createRequest')->willReturn(new Request('POST', 'http://something.com'));

        $httpResponse = new Response(400, [], null);
        $this->client->method('sendRequest')->willReturn($httpResponse);
        try {
            $this->nSureClient->sendEventPhoneVerification($request);
            static::fail();
        } catch (NSureClientException $e) {
            self::assertSame('REQUEST_EXCEPTION', $e->getErrorCode());
        }
    }

    public function testGetEventPhoneVerificationSuccess(): void
    {
        $request = $this->getPhoneVerificationEventRequest();
        $responseDto = new SendEventResponse(true);
        $this->mockSendRequestSuccessfully($responseDto);
        $response = $this->nSureClient->sendEventPhoneVerification($request);

        self::assertSame($response->isOk(), $responseDto->isOk());
    }

    public function testGetEventEmailVerificationSuccess(): void
    {
        $request = $this->getEmailVerificationEventRequest();
        $responseDto = new SendEventResponse(true);
        $this->mockSendRequestSuccessfully($responseDto);
        $response = $this->nSureClient->sendEventEmailVerification($request);

        self::assertSame($response->isOk(), $responseDto->isOk());
    }

    public function testGetEventSignUpSuccess(): void
    {
        $request = $this->getSignUpEventRequest();
        $responseDto = new SendEventResponse(true);
        $this->mockSendRequestSuccessfully($responseDto);
        $response = $this->nSureClient->sendSignUpEvent($request);

        self::assertSame($response->isOk(), $responseDto->isOk());
    }

    public function testGetEventSignInSuccess(): void
    {
        $request = $this->getSignInEventRequest();
        $responseDto = new SendEventResponse(true);
        $this->mockSendRequestSuccessfully($responseDto);
        $response = $this->nSureClient->sendSignInEvent($request);

        self::assertSame($response->isOk(), $responseDto->isOk());
    }

    public function testGetEventSignOutSuccess(): void
    {
        $request = $this->getSignOutEventRequest();
        $responseDto = new SendEventResponse(true);
        $this->mockSendRequestSuccessfully($responseDto);
        $response = $this->nSureClient->sendSignOutEvent($request);

        self::assertSame($response->isOk(), $responseDto->isOk());
    }

    public function testWrongDenormalize(): void
    {
        $request = $this->getPhoneVerificationEventRequest();
        $this->requestFactory->method('createRequest')->willReturn(new Request('POST', 'http://something.com'));

        $httpResponse = new Response(200, [], null);
        $this->client->method('sendRequest')->willReturn($httpResponse);

        $this->serializer->method('denormalize')->willThrowException(new NotNormalizableValueException());
        try {
            $this->nSureClient->sendEventPhoneVerification($request);
            static::fail();
        } catch (NSureClientException $e) {
            self::assertSame(NSureClientException::CODE_BAD_RESPONSE, $e->getErrorCode());
            self::assertSame('Can not denormalize response: ', $e->getMessage());
        }
    }

    public function testSendMerchantFinalDecisionSuccess(): void
    {
        $responseDto = new SendEventResponse(true);
        $this->mockSendRequestSuccessfully($responseDto);
        $response = $this->nSureClient->sendMerchantFinalDecision($this->getMerchantFinalDecisionRequest());

        self::assertSame($response->isOk(), $responseDto->isOk());
    }

    public function testSendTxFailureSuccess(): void
    {
        $responseDto = new SendEventResponse(true);
        $this->mockSendRequestSuccessfully($responseDto);
        $response = $this->nSureClient->sendTxFailureEvent($this->getTxFailureEventRequest());

        self::assertSame($response->isOk(), $responseDto->isOk());
    }

    public function testPaymentMethodEventSuccess(): void
    {
        $responseDto = new SendEventResponse(true);
        $this->mockSendRequestSuccessfully($responseDto);
        $response = $this->nSureClient->sendPaymentMethodEvent($this->getPaymentMethodEventRequest());

        self::assertSame($response->isOk(), $responseDto->isOk());
    }

    private function mockSendRequestSuccessfully(SendEventResponse $responseDto): void
    {
        $this->requestFactory->method('createRequest')->willReturn(new Request('POST', 'http://something.com'));
        $httpResponse = new Response(200, [], null);
        $this->client->expects(static::once())->method('sendRequest')->willReturn($httpResponse);

        $this->serializer->method('denormalize')->willReturn($responseDto);
    }

    private function getPaymentMethodEventRequest(): PaymentMethodEventRequest
    {
        return (new EventRequestFactory())->createPaymentMethodEventRequest(
            Timestamp::fromInt(time()),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            Bin::fromString('666666'),
            CardType::fromString('credit'),
            CardholderName::fromString('Vasya Pupkin'),
            false,
            Last4::fromString('1234')
        );
    }

    private function getPhoneVerificationEventRequest(): PhoneVerificationEventRequest
    {
        return (new EventRequestFactory())->createPhoneVerificationEventRequest(
            Timestamp::fromInt(time()),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('13601e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            PhoneNumber::fromString('5211178455'),
            PhoneCountryCode::fromString('44')
        );
    }

    private function getEmailVerificationEventRequest(): EmailVerificationEventRequest
    {
        return (new EventRequestFactory())->createEmailVerificationEventRequest(
            Timestamp::fromInt(time()),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            Email::fromString('c.kent@example.com')
        );
    }

    private function getMerchantFinalDecisionRequest(): MerchantFinalDecisionEventRequest
    {
        return new MerchantFinalDecisionEventRequest(
            Decision::fromString(self::ACCEPTED),
            TransactionId::fromInt(123),
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(1640693579),
            ClientRequestId::fromString((Uuid::uuid4()->toString())),
            []
        );
    }

    private function getTxFailureEventRequest(): TxFailureEventRequest
    {
        return new TxFailureEventRequest(
            EventId::fromString(self::EVENT_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            ClientRequestId::fromString(self::CLIENT_REQUEST_ID),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
            Email::fromString(self::EMAIL),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            FailureReason::fromString(self::FAILURE_REASON),
            TransactionId::fromInt(self::TRANSACTION_ID),
            new Payment(
                Amount::fromString(self::PAYMENT_AMOUNT),
                Currency::fromString(self::PAYMENT_CURRENCY),
            ),
            new Payout(
                Amount::fromString(self::PAYOUT_AMOUNT),
                Currency::fromString(self::PAYOUT_CURRENCY),
                ExchangeRate::fromString(self::EXCHANGE_RATE),
            ),
            Gateway::fromString(self::GATEWAY),
            new Card(
                Card\Bin::fromString(self::BIN),
                Card\LastFourDigits::fromString(self::LAST4),
                Card\CardholderName::fromString(self::CARDHOLDER_NAME),
                Card\CountryCode::fromString(self::CARD_COUNTRY),
                Card\Scheme::fromString(self::SCHEME),
                self::IS_DEBIT
            ),
            null
        );
    }

    private function getSignUpEventRequest(): SignUpEventRequest
    {
        return (new EventRequestFactory())->createSignUpEventRequest(
            Timestamp::fromInt(time()),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            Email::fromString('c.kent@example.com'),
            PhoneNumber::fromString('5211178455'),
            PhoneCountryCode::fromString('44')
        );
    }

    private function getSignInEventRequest(): SignInEventRequest
    {
        return (new EventRequestFactory())->createSignInEventRequest(
            Timestamp::fromInt(time()),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58')
        );
    }

    private function getSignOutEventRequest(): SignOutEventRequest
    {
        return (new EventRequestFactory())->createSignOutEventRequest(
            Timestamp::fromInt(time()),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58')
        );
    }
}
