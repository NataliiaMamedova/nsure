<?php

declare(strict_types=1);

namespace App\Tests\Unit\NSure\Service;

use App\DTO\TransactionCheckInfo;
use App\Exception\AccessDeniedException;
use App\Exception\NotFoundException;
use App\Exception\NSureClientException;
use App\Exception\NSureImplementationException;
use App\Exception\NSureServiceException;
use App\NSure\Factory\EventRequestFactory;
use App\NSure\NSureClient;
use App\NSure\Request\AbstractNSureRequest;
use App\NSure\Request\EmailVerificationEventRequest;
use App\NSure\Request\MerchantFinalDecisionEventRequest;
use App\NSure\Request\PaymentMethodEventRequest;
use App\NSure\Request\PhoneVerificationEventRequest;
use App\NSure\Request\RecipientUpdateEventRequest;
use App\NSure\Request\SignInEventRequest;
use App\NSure\Request\SignOutEventRequest;
use App\NSure\Request\SignUpEventRequest;
use App\NSure\Request\TxCancelEventRequest;
use App\NSure\Request\TxFailureEventRequest;
use App\NSure\Service\NSureService;
use App\Repository\ProcessingTransactionRepositoryInterface;
use App\VO\Amount;
use App\VO\Bin;
use App\VO\CancelReason;
use App\VO\CardholderName;
use App\VO\CardType;
use App\VO\ClientRequestId;
use App\VO\Currency;
use App\VO\Decision;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\ExchangeRate;
use App\VO\FailureReason;
use App\VO\FirstName;
use App\VO\Gateway;
use App\VO\Invoice;
use App\VO\IP;
use App\VO\Last4;
use App\VO\LastName;
use App\VO\Payment;
use App\VO\PaymentMethod\Alternative;
use App\VO\PaymentMethod\Alternative\Type;
use App\VO\PaymentMethod\Card;
use App\VO\PaymentMethod\Card\CountryCode;
use App\VO\PaymentMethod\Card\LastFourDigits;
use App\VO\PaymentMethod\Card\Scheme;
use App\VO\Payout;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserAgent;
use App\VO\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
class NSureServiceTest extends TestCase
{
    private const TRANSACTION_ID = 123;

    private const INVOICE = 'invoice';

    private const USER_ID = 810;

    private const TIMESTAMP = 1640693579;

    private const ACCEPTED = 'Accepted';

    private const PAYMENT_AMOUNT = '351.75';

    private const PAYMENT_CURRENCY = 'USD';

    private const PAYOUT_AMOUNT = '3.5';

    private const PAYOUT_CURRENCY = 'BTC';

    private const EXCHANGE_RATE = '100.5';

    private const GATEWAY = 'worldpay';

    private const BIN = '123456';

    private const LAST4 = '6789';

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

    private const ALTERNATIVE_TYPE = 'bankTransfer';

    private const CANCEL_REASON = 'userFraud';

    private ?NSureService $service = null;

    /**
     * @var NSureClient|MockObject
     */
    private MockObject $httpClient;

    /**
     * @var EventRequestFactory|MockObject
     */
    private MockObject $eventRequestFactory;

    /**
     * @var MockObject|LoggerInterface
     */
    private MockObject $logger;

    /**
     * @var MockObject|ProcessingTransactionRepositoryInterface
     */
    private MockObject $processingTransactionRepository;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(NSureClient::class);
        $this->eventRequestFactory = $this->createMock(EventRequestFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->processingTransactionRepository = $this->createMock(ProcessingTransactionRepositoryInterface::class);
        $this->service = new NSureService(
            $this->httpClient,
            $this->eventRequestFactory,
            $this->processingTransactionRepository,
            $this->logger,
        );
    }

    protected function tearDown(): void
    {
        $this->service = null;
    }

    public function testMerchantFinalDecisionSuccess(): void
    {
        $merchantFinalDecisionEventRequest = $this->getMerchantFinalDecisionEventRequest();
        $this->mockSendRequestSuccessfully('createMerchantFinalDecisionRequest', 'sendMerchantFinalDecision', $merchantFinalDecisionEventRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processMerchantFinalDecisionEvent(
            Decision::fromString(self::ACCEPTED),
            TransactionId::fromInt(self::TRANSACTION_ID),
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP)
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testMerchantFinalDecisionClientException(\Throwable $throwable): void
    {
        $merchantFinalDecisionEventRequest = $this->getMerchantFinalDecisionEventRequest();
        $this->mockSendRequestWithException('createMerchantFinalDecisionRequest', 'sendMerchantFinalDecision', $merchantFinalDecisionEventRequest, $throwable);

        $this->expectException(NSureServiceException::class);

        $requestedBody = [
            'metadata' => [
                'type' => 'merchantFinalDecision',
                'timestamp' => 1640693579,
                'clientUserId' => self::USER_ID,
                'clientRequestId' => '123',
            ],
            'merchantFinalDecision' => [
                'decision' => 'Accepted',
                'txId' => '123',
                'rawGatewayData' => [],
            ],
        ];
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processMerchantFinalDecisionEvent(
            Decision::fromString(self::ACCEPTED),
            TransactionId::fromInt(self::TRANSACTION_ID),
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP)
        );
    }

    public function testTxFailureEventSuccess(): void
    {
        $txFailureEventRequest = $this->getTxFailureEventRequest();

        $this->processingTransactionRepository
            ->expects($this->once())
            ->method('getTransactionCheckInfoByInvoice')
            ->willReturn(
                new TransactionCheckInfo(
                    TransactionId::fromInt(self::TRANSACTION_ID),
                    new Payment(
                        Amount::fromString(self::PAYMENT_AMOUNT),
                        Currency::fromString(self::PAYMENT_CURRENCY)
                    ),
                    new Payout(
                        Amount::fromString(self::PAYOUT_AMOUNT),
                        Currency::fromString(self::PAYOUT_AMOUNT),
                        ExchangeRate::fromString(self::EXCHANGE_RATE),
                    ),
                    Gateway::fromString(self::GATEWAY),
                    new Card(
                        Card\Bin::fromString(self::BIN),
                        LastFourDigits::fromString(self::LAST4),
                        Card\CardholderName::fromString(self::CARDHOLDER_NAME),
                        CountryCode::fromString(self::CARD_COUNTRY),
                        Scheme::fromString(self::SCHEME),
                        self::IS_DEBIT
                    ),
                    new Alternative(
                        Type::fromString(self::ALTERNATIVE_TYPE)
                    ),
                )
            )
        ;

        $this->mockSendRequestSuccessfully('createTxFailureEventRequest', 'sendTxFailureEvent', $txFailureEventRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processTxFailure(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            Invoice::fromString(self::INVOICE),
            FailureReason::fromString(self::FAILURE_REASON),
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testTxFailureClientException(\Throwable $throwable): void
    {
        $txFailureEventRequest = $this->getTxFailureEventRequest();
        $this->mockSendRequestWithException('createTxFailureEventRequest', 'sendTxFailureEvent', $txFailureEventRequest, $throwable);

        $this->processingTransactionRepository
            ->expects($this->once())
            ->method('getTransactionCheckInfoByInvoice')
            ->willReturn(
                new TransactionCheckInfo(
                    TransactionId::fromInt(self::TRANSACTION_ID),
                    new Payment(
                        Amount::fromString(self::PAYMENT_AMOUNT),
                        Currency::fromString(self::PAYMENT_CURRENCY)
                    ),
                    new Payout(
                        Amount::fromString(self::PAYOUT_AMOUNT),
                        Currency::fromString(self::PAYOUT_AMOUNT),
                        ExchangeRate::fromString(self::EXCHANGE_RATE),
                    ),
                    Gateway::fromString(self::GATEWAY),
                    new Card(
                        Card\Bin::fromString(self::BIN),
                        LastFourDigits::fromString(self::LAST4),
                        Card\CardholderName::fromString(self::CARDHOLDER_NAME),
                        CountryCode::fromString(self::CARD_COUNTRY),
                        Scheme::fromString(self::SCHEME),
                        self::IS_DEBIT
                    ),
                    new Alternative(
                        Type::fromString(self::ALTERNATIVE_TYPE)
                    ),
                )
            )
        ;
        $this->expectException(NSureServiceException::class);

        $requestedBody = \json_decode($txFailureEventRequest->makeBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processTxFailure(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            Invoice::fromString(self::INVOICE),
            FailureReason::fromString(self::FAILURE_REASON),
        );
    }

    /**
     * @dataProvider provideProcessingApiClientExceptions
     */
    public function testTxFailureProcessingApiClientException(\Throwable $throwable): void
    {
        $this->processingTransactionRepository
            ->expects($this->once())
            ->method('getTransactionCheckInfoByInvoice')
            ->willThrowException($throwable)
        ;

        $this->expectException(\get_class($throwable));

        /** @phpstan-ignore-next-line */
        $this->service->processTxFailure(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            Invoice::fromString(self::INVOICE),
            FailureReason::fromString(self::FAILURE_REASON),
        );
    }

    public function provideProcessingApiClientExceptions(): iterable
    {
        return [
            'forbidden' => [new AccessDeniedException('forbidden')],
            'not found' => [new NotFoundException('not_found')],
        ];
    }

    public function provideClientExceptions(): iterable
    {
        return [
            'implementation' => [new NSureImplementationException('test', 'impl')],
            'client' => [new NSureClientException('test2', 'client')],
        ];
    }

    public function testPhoneVerificationSuccess(): void
    {
        $userId = 810;
        $phoneVerificationRequest = $this->getPhoneVerificationEventRequest();
        $this->mockSendRequestSuccessfully('createPhoneVerificationEventRequest', 'sendEventPhoneVerification', $phoneVerificationRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processPhoneVerificationEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt($userId),
            DeviceId::fromString('13601e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            PhoneNumber::fromString('5211178455'),
            PhoneCountryCode::fromString('44')
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testPhoneVerificationClientException(\Throwable $throwable): void
    {
        $userId = 810;
        $phoneVerificationRequest = $this->getPhoneVerificationEventRequest();
        $this->mockSendRequestWithException('createPhoneVerificationEventRequest', 'sendEventPhoneVerification', $phoneVerificationRequest, $throwable);

        $this->expectException(NSureServiceException::class);

        $requestedBody = \json_decode($phoneVerificationRequest->makeBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processPhoneVerificationEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt($userId),
            DeviceId::fromString('13601e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            PhoneNumber::fromString('5211178455'),
            PhoneCountryCode::fromString('44')
        );
    }

    public function testRecipientUpdateSuccess(): void
    {
        $recipientUpdateEventRequest = $this->createRecipientUpdateEventRequest();
        $this->mockSendRequestSuccessfully('createRecipientUpdateEventRequest', 'sendRecipientUpdateEvent', $recipientUpdateEventRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processRecipientUpdateEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            TransactionId::fromInt(self::TRANSACTION_ID),
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testRecipientUpdateClientException(\Throwable $throwable): void
    {
        $recipientUpdateEventRequest = $this->createRecipientUpdateEventRequest();
        $this->mockSendRequestWithException('createRecipientUpdateEventRequest', 'sendRecipientUpdateEvent', $recipientUpdateEventRequest, $throwable);

        $this->expectException(NSureServiceException::class);

        $requestedBody = \json_decode($recipientUpdateEventRequest->makeBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processRecipientUpdateEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            TransactionId::fromInt(self::TRANSACTION_ID),
        );
    }

    public function testEmailVerificationEventSuccess(): void
    {
        $userId = 810;
        $emailVerificationRequest = $this->getEmailVerificationEventRequest();
        $this->mockSendRequestSuccessfully('createEmailVerificationEventRequest', 'sendEventEmailVerification', $emailVerificationRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processEmailVerificationEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt($userId),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            Email::fromString('c.kent@example.com')
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testEmailVerificationEventClientException(\Throwable $throwable): void
    {
        $userId = 810;
        $emailVerificationRequest = $this->getEmailVerificationEventRequest();
        $this->mockSendRequestWithException('createEmailVerificationEventRequest', 'sendEventEmailVerification', $emailVerificationRequest, $throwable);

        $this->expectException(NSureServiceException::class);

        $requestedBody = \json_decode($emailVerificationRequest->makeBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processEmailVerificationEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt($userId),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            Email::fromString('c.kent@example.com')
        );
    }

    public function testSignUpEventSuccess(): void
    {
        $userId = 810;
        $signUpRequest = $this->getSignUpEventRequest();
        $this->mockSendRequestSuccessfully('createSignUpEventRequest', 'sendSignUpEvent', $signUpRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processSignUpEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt($userId),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            Email::fromString('c.kent@example.com'),
            PhoneNumber::fromString('5211178455'),
            PhoneCountryCode::fromString('44')
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testSignUpEventClientException(\Throwable $throwable): void
    {
        $signUpRequest = $this->getSignUpEventRequest();
        $this->mockSendRequestWithException('createSignUpEventRequest', 'sendSignUpEvent', $signUpRequest, $throwable);

        $this->expectException(NSureServiceException::class);

        $requestedBody = \json_decode($signUpRequest->makeBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processSignUpEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            Email::fromString('c.kent@example.com'),
            PhoneNumber::fromString('5211178455'),
            PhoneCountryCode::fromString('44')
        );
    }

    public function testSignInEventSuccess(): void
    {
        $userId = Uuid::uuid4()->toString();
        $signInRequest = $this->getSignInEventRequest();
        $this->mockSendRequestSuccessfully('createSignInEventRequest', 'sendSignInEvent', $signInRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processSignInEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58')
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testSignInEventClientException(\Throwable $throwable): void
    {
        $userId = Uuid::uuid4()->toString();
        $signInRequest = $this->getSignInEventRequest();
        $this->mockSendRequestWithException('createSignInEventRequest', 'sendSignInEvent', $signInRequest, $throwable);

        $this->expectException(NSureServiceException::class);

        $requestedBody = \json_decode($signInRequest->makeBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processSignInEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58')
        );
    }

    public function testTxCancelEventSuccess(): void
    {
        $txCancelEventRequest = $this->getTxCancelEventRequest();
        $this->mockSendRequestSuccessfully('createTxCancelRequest', 'sendTxCancelEvent', $txCancelEventRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processTxCancel(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            CancelReason::fromString(self::CANCEL_REASON),
            TransactionId::fromInt(self::TRANSACTION_ID),
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testTxCancelEventClientException(\Throwable $throwable): void
    {
        $txCancelEventRequest = $this->getTxCancelEventRequest();
        $this->mockSendRequestWithException('createTxCancelRequest', 'sendTxCancelEvent', $txCancelEventRequest, $throwable);

        $this->expectException(NSureServiceException::class);

        $requestedBody = \json_decode($txCancelEventRequest->makeBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processTxCancel(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            CancelReason::fromString(self::CANCEL_REASON),
            TransactionId::fromInt(self::TRANSACTION_ID),
        );
    }

    public function testSignOutEventSuccess(): void
    {
        $signOutRequest = $this->getSignOutEventRequest();
        $this->mockSendRequestSuccessfully('createSignOutEventRequest', 'sendSignOutEvent', $signOutRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processSignOutEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58')
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testSignOutEventClientException(\Throwable $throwable): void
    {
        $signOutRequest = $this->getSignOutEventRequest();
        $this->mockSendRequestWithException('createSignOutEventRequest', 'sendSignOutEvent', $signOutRequest, $throwable);

        $this->expectException(NSureServiceException::class);

        $requestedBody = \json_decode($signOutRequest->makeBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processSignOutEvent(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58')
        );
    }

    public function testPaymentMethodEventSuccess(): void
    {
        $paymentMethodEventRequest = $this->getPaymentMethodEventRequest();
        $this->mockSendRequestSuccessfully('createPaymentMethodEventRequest', 'sendPaymentMethodEvent', $paymentMethodEventRequest);

        /** @phpstan-ignore-next-line */
        $this->service->processPaymentMethodEvent(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            Last4::fromString('1234'),
            Bin::fromString('666666'),
            CardholderName::fromString('Vasya Pupkin'),
            CardType::fromString('credit'),
            false,
            IP::fromString('192.168.0.58'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986')
        );
    }

    /**
     * @dataProvider provideClientExceptions
     */
    public function testPaymentMethodEventClientException(\Throwable $throwable): void
    {
        $paymentMethodEventRequest = $this->getPaymentMethodEventRequest();
        $this->mockSendRequestWithException('createPaymentMethodEventRequest', 'sendPaymentMethodEvent', $paymentMethodEventRequest, $throwable);

        $this->expectException(NSureServiceException::class);

        $requestedBody = \json_decode($paymentMethodEventRequest->makeBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->loggerExecute($throwable, $requestedBody);

        /** @phpstan-ignore-next-line */
        $this->service->processPaymentMethodEvent(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            Last4::fromString('1234'),
            Bin::fromString('666666'),
            CardholderName::fromString('Vasya Pupkin'),
            CardType::fromString('credit'),
            false,
            IP::fromString('192.168.0.58'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986')
        );
    }

    private function mockSendRequestSuccessfully(string $createRequestMethodName, string $sendRequestMethodName, AbstractNSureRequest $request): void
    {
        $this->eventRequestFactory
            ->expects($this->once())
            ->method($createRequestMethodName)
            ->willReturn(
                $request
            )
        ;

        $this->httpClient
            ->expects($this->once())
            ->method($sendRequestMethodName)
            ->with($request)
        ;
    }

    private function mockSendRequestWithException(
        string $createRequestMethodName,
        string $sendRequestMethodName,
        AbstractNSureRequest $request,
        \Throwable $e
    ): void {
        $this->eventRequestFactory
            ->expects($this->once())
            ->method($createRequestMethodName)
            ->willReturn(
                $request
            )
        ;

        $this->httpClient
            ->expects($this->once())
            ->method($sendRequestMethodName)
            ->with($request)
            ->willThrowException($e)
        ;
    }

    private function getMerchantFinalDecisionEventRequest(): MerchantFinalDecisionEventRequest
    {
        return new MerchantFinalDecisionEventRequest(
            Decision::fromString(self::ACCEPTED),
            TransactionId::fromInt(self::TRANSACTION_ID),
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            ClientRequestId::fromString('123')
        );
    }

    private function getEmailVerificationEventRequest(): EmailVerificationEventRequest
    {
        return (new EventRequestFactory())->createEmailVerificationEventRequest(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            Email::fromString('c.kent@example.com')
        );
    }

    private function getTxFailureEventRequest(): TxFailureEventRequest
    {
        return (new EventRequestFactory())->createTxFailureEventRequest(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
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

    private function getPhoneVerificationEventRequest(): PhoneVerificationEventRequest
    {
        return (new EventRequestFactory())->createPhoneVerificationEventRequest(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('13601e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58'),
            PhoneNumber::fromString('5211178455'),
            PhoneCountryCode::fromString('44')
        );
    }

    private function createRecipientUpdateEventRequest(): RecipientUpdateEventRequest
    {
        return (new EventRequestFactory())->createRecipientUpdateEventRequest(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString(self::DEVICE_ID),
            UserAgent::fromString(self::USER_AGENT),
            IP::fromString(self::IP),
            Email::fromString(self::EMAIL),
            FirstName::fromString(self::FIRST_NAME),
            LastName::fromString(self::LAST_NAME),
            PhoneNumber::fromString(self::PHONE),
            PhoneCountryCode::fromString(self::PHONE_CODE),
            TransactionId::fromInt(self::TRANSACTION_ID),
        );
    }

    private function getSignUpEventRequest(): SignUpEventRequest
    {
        return (new EventRequestFactory())->createSignUpEventRequest(
            Timestamp::fromInt(self::TIMESTAMP),
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
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58')
        );
    }

    private function getTxCancelEventRequest(): TxCancelEventRequest
    {
        return (new EventRequestFactory())->createTxCancelRequest(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            CancelReason::fromString(self::CANCEL_REASON),
            TransactionId::fromInt(self::TRANSACTION_ID),
        );
    }

    private function getSignOutEventRequest(): SignOutEventRequest
    {
        return (new EventRequestFactory())->createSignOutEventRequest(
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            DeviceId::fromString('136016b3-e0bc-4c85-a453-545b112c5986'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            IP::fromString('192.168.0.58')
        );
    }

    private function getPaymentMethodEventRequest(): PaymentMethodEventRequest
    {
        return (new EventRequestFactory())->createPaymentMethodEventRequest(
            Timestamp::fromInt(self::TIMESTAMP),
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

    private function loggerExecute(\Throwable $throwable, array $requestBody): void
    {
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                sprintf(
                    '%s error: {message}',
                    $throwable instanceof NSureImplementationException ? 'implementation' : 'client',
                ),
                [
                    'message' => $throwable->getMessage(),
                    'request_body' => $requestBody,
                ]
            )
        ;
    }
}
