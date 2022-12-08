<?php

declare(strict_types=1);

namespace App\Tests\Unit\NSure\Request;

use App\NSure\Request\TxFailureEventRequest;
use App\VO\Amount;
use App\VO\ClientRequestId;
use App\VO\Currency;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\EventId;
use App\VO\ExchangeRate;
use App\VO\FailureReason;
use App\VO\FirstName;
use App\VO\Gateway;
use App\VO\IP;
use App\VO\LastName;
use App\VO\Payment;
use App\VO\PaymentMethod\Alternative;
use App\VO\PaymentMethod\Alternative\Type;
use App\VO\PaymentMethod\Card;
use App\VO\Payout;
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
class TxFailureEventRequestTest extends TestCase
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

    private const USER_AGENT = 'agent';

    private const IP = '0.0.0.0';

    private const FIRST_NAME = 'first';

    private const LAST_NAME = 'last';

    private const EMAIL = 'email@example.com';

    private const PAYMENT_METHOD_BANK_TRANSFER = 'bankTransfer';

    private const FAILURE_REASON = 'processorDeclined';

    private const CLIENT_REQUEST_ID = 'd7aa909b-d2ba-40ff-8c79-2f898517a7ea';

    /**
     * @dataProvider provideCases
     */
    public function testSuccess(?Gateway $gateway, ?Card $card = null, ?Alternative $alternative = null, array $expectedBody): void
    {
        $request = new TxFailureEventRequest(
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
            $card,
            $alternative
        );

        self::assertSame($expectedBody, \json_decode($request->makeBody(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function provideCases(): iterable
    {
        yield 'empty payment method' => [Gateway::fromString(self::GATEWAY), null, null, $this->getDefaultExpectedBody()];

        $expectedBody = $this->getDefaultExpectedBody();
        unset($expectedBody['transactionDetails']['paymentMethodDetails']['gateway']);
        yield 'empty payment method and empty gateway' => [Gateway::fromString(self::GATEWAY), null, null, $this->getDefaultExpectedBody()];

        $expectedBody = $this->getDefaultExpectedBody();
        unset($expectedBody['transactionDetails']['paymentMethodDetails']['alternative']);
        $expectedBody['transactionDetails']['paymentMethodDetails']['creditCard'] = [
            'bin' => self::BIN,
            'last4' => self::LAST4,
            'cardHolderName' => self::CARDHOLDER_NAME,
            'countryCode' => self::CARD_COUNTRY,
            'cardType' => self::SCHEME,
            'isDebit' => self::IS_DEBIT,
        ];
        yield 'card payment method' => [
            Gateway::fromString(self::GATEWAY),
            new Card(
                Card\Bin::fromString(self::BIN),
                Card\LastFourDigits::fromString(self::LAST4),
                Card\CardholderName::fromString(self::CARDHOLDER_NAME),
                Card\CountryCode::fromString(self::CARD_COUNTRY),
                Card\Scheme::fromString(self::SCHEME),
                self::IS_DEBIT
            ),
            null,
            $expectedBody,
        ];

        $expectedBody = $this->getDefaultExpectedBody();
        unset($expectedBody['transactionDetails']['paymentMethodDetails']['alternative']);
        $expectedBody['transactionDetails']['paymentMethodDetails']['creditCard'] = [
            'bin' => self::BIN,
            'last4' => self::LAST4,
            'cardHolderName' => self::CARDHOLDER_NAME,
            'countryCode' => self::CARD_COUNTRY,
            'cardType' => self::SCHEME,
            'isDebit' => self::IS_DEBIT,
        ];
        yield 'card payment method and alternative given, card has preference' => [
            Gateway::fromString(self::GATEWAY),
            new Card(
                Card\Bin::fromString(self::BIN),
                Card\LastFourDigits::fromString(self::LAST4),
                Card\CardholderName::fromString(self::CARDHOLDER_NAME),
                Card\CountryCode::fromString(self::CARD_COUNTRY),
                Card\Scheme::fromString(self::SCHEME),
                self::IS_DEBIT
            ),
            new Alternative(Type::fromString(self::PAYMENT_METHOD_BANK_TRANSFER)),
            $expectedBody,
        ];

        $expectedBody = $this->getDefaultExpectedBody();
        $expectedBody['transactionDetails']['paymentMethodDetails']['alternative']['type'] = self::PAYMENT_METHOD_BANK_TRANSFER;

        yield 'bank transfer payment method' => [
            Gateway::fromString(self::GATEWAY),
            null,
            new Alternative(Type::fromString(self::PAYMENT_METHOD_BANK_TRANSFER)),
            $expectedBody,
        ];
    }

    private function getDefaultExpectedBody(): array
    {
        return [
            'metadata' => [
                'type' => self::EVENT_ID,
                'timestamp' => self::TIMESTAMP,
                'clientUserId' => (string) self::USER_ID,
                'clientRequestId' => self::CLIENT_REQUEST_ID,
            ],
            'sessionInfo' => [
                'deviceId' => self::DEVICE_ID,
                'userAgent' => self::USER_AGENT,
                'ip' => self::IP,
            ],
            'personalInfo' => [
                'email' => self::EMAIL,
                'firstName' => self::FIRST_NAME,
                'lastName' => self::LAST_NAME,
                'phoneInfo' => [
                    'phone' => self::PHONE,
                    'countryCode' => self::PHONE_CODE,
                ],
            ],
            'recipientInfo' => [
                'email' => self::EMAIL,
                'firstName' => self::FIRST_NAME,
                'lastName' => self::LAST_NAME,
                'phoneInfo' => [
                    'phone' => self::PHONE,
                    'countryCode' => self::PHONE_CODE,
                ],
            ],
            'transactionDetails' => [
                'txId' => (string) self::TRANSACTION_ID,
                'paidAmount' => [
                    'valueInCurrency' => (float) self::PAYMENT_AMOUNT,
                    'currency' => self::PAYMENT_CURRENCY,
                ],
                'paymentMethodDetails' => [
                    'gateway' => self::GATEWAY,
                    'alternative' => [
                        'type' => 'other',
                    ],
                ],
                'cart' => [
                    0 => [
                        'brand' => self::PAYOUT_CURRENCY,
                        'quantity' => (float) self::PAYOUT_AMOUNT,
                        'itemFulfillment' => 'digital',
                        'sellingPrice' => [
                            'valueInCurrency' => (float) self::EXCHANGE_RATE,
                            'currency' => self::PAYMENT_CURRENCY,
                        ],
                    ],
                ],
                'failureReason' => self::FAILURE_REASON,
            ],
        ];
    }
}
