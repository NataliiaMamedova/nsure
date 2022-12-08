<?php

declare(strict_types=1);

namespace App\NSure\Factory;

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
use App\VO\Bin;
use App\VO\CancelReason;
use App\VO\CardholderName;
use App\VO\CardType;
use App\VO\ClientRequestId;
use App\VO\Decision;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\EventId;
use App\VO\FailureReason;
use App\VO\FirstName;
use App\VO\Gateway;
use App\VO\IP;
use App\VO\Last4;
use App\VO\LastName;
use App\VO\Payment;
use App\VO\PaymentMethod\Alternative;
use App\VO\PaymentMethod\Card;
use App\VO\Payout;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserAgent;
use App\VO\UserId;
use Ramsey\Uuid\Uuid;

class EventRequestFactory
{
    public function createPhoneVerificationEventRequest(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode
    ): PhoneVerificationEventRequest {
        $clientRequestId = ClientRequestId::fromString(Uuid::uuid4()->toString());

        return new PhoneVerificationEventRequest(
            EventId::phoneVerification(),
            $timestamp,
            $userId,
            $clientRequestId,
            $deviceId,
            $userAgent,
            $ip,
            $phoneNumber,
            $phoneCountryCode
        );
    }

    public function createEmailVerificationEventRequest(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Email $email
    ): EmailVerificationEventRequest {
        $clientRequestId = ClientRequestId::fromString(Uuid::uuid4()->toString());

        return new EmailVerificationEventRequest(
            EventId::emailVerification(),
            $timestamp,
            $userId,
            $clientRequestId,
            $deviceId,
            $userAgent,
            $ip,
            $email
        );
    }

    public function createMerchantFinalDecisionRequest(
        Decision $decision,
        TransactionId $transactionId,
        UserId $userId,
        Timestamp $timestamp,
        array $gatewayData = [],
    ): MerchantFinalDecisionEventRequest {
        return new MerchantFinalDecisionEventRequest(
            $decision,
            $transactionId,
            $userId,
            $timestamp,
            ClientRequestId::fromString(Uuid::uuid4()->toString()),
            $gatewayData
        );
    }

    public function createSignUpEventRequest(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Email $email,
        PhoneNumber $phone,
        PhoneCountryCode $countryCode
    ): SignUpEventRequest {
        $clientRequestId = ClientRequestId::fromString(Uuid::uuid4()->toString());

        return new SignUpEventRequest(
            EventId::signUp(),
            $timestamp,
            $userId,
            $clientRequestId,
            $deviceId,
            $userAgent,
            $ip,
            $email,
            $phone,
            $countryCode
        );
    }

    public function createPaymentMethodEventRequest(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Bin $bin,
        CardType $cardType,
        CardholderName $cardholderName,
        bool $isDebit,
        Last4 $last4
    ): PaymentMethodEventRequest {
        return new PaymentMethodEventRequest(
            EventId::paymentMethod(),
            $timestamp,
            $userId,
            ClientRequestId::fromString(Uuid::uuid4()->toString()),
            $deviceId,
            $userAgent,
            $ip,
            $bin,
            $cardType,
            $cardholderName,
            $isDebit,
            $last4
        );
    }

    public function createTxFailureEventRequest(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Email $email,
        FirstName $firstName,
        LastName $lastName,
        PhoneNumber $phone,
        PhoneCountryCode $countryCode,
        FailureReason $failureReason,
        TransactionId $transactionId,
        Payment $payment,
        Payout $payout,
        ?Gateway $gateway,
        ?Card $card,
        ?Alternative $alternative,
    ): TxFailureEventRequest {
        $clientRequestId = ClientRequestId::fromString(Uuid::uuid4()->toString());

        return new TxFailureEventRequest(
            EventId::txFailure(),
            $timestamp,
            $userId,
            $clientRequestId,
            $deviceId,
            $userAgent,
            $ip,
            $email,
            $phone,
            $countryCode,
            $firstName,
            $lastName,
            $failureReason,
            $transactionId,
            $payment,
            $payout,
            $gateway,
            $card,
            $alternative,
        );
    }

    public function createSignInEventRequest(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip
    ): SignInEventRequest {
        $clientRequestId = ClientRequestId::fromString(Uuid::uuid4()->toString());

        return new SignInEventRequest(
            $userId,
            $ip,
            $userAgent,
            $deviceId,
            $timestamp,
            EventId::signIn(),
            $clientRequestId
        );
    }

    public function createRecipientUpdateEventRequest(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Email $email,
        FirstName $firstName,
        LastName $lastName,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode,
        ?TransactionId $transactionId,
    ): RecipientUpdateEventRequest {
        return new RecipientUpdateEventRequest(
            $userId,
            $ip,
            $userAgent,
            $deviceId,
            $timestamp,
            EventId::recipientUpdate(),
            ClientRequestId::fromString(Uuid::uuid4()->toString()),
            $email,
            $firstName,
            $lastName,
            $phoneNumber,
            $phoneCountryCode,
            $transactionId,
        );
    }

    public function createSignOutEventRequest(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip
    ): SignOutEventRequest {
        $clientRequestId = ClientRequestId::fromString(Uuid::uuid4()->toString());

        return new SignOutEventRequest(
            $userId,
            $ip,
            $userAgent,
            $deviceId,
            $timestamp,
            EventId::signOut(),
            $clientRequestId
        );
    }

    public function createTxCancelRequest(
        Timestamp $timestamp,
        UserId $userId,
        CancelReason $cancelReason,
        TransactionId $transactionId,
    ): TxCancelEventRequest {
        $clientRequestId = ClientRequestId::fromString(Uuid::uuid4()->toString());

        return new TxCancelEventRequest(
            EventId::txCancel(),
            $timestamp,
            $userId,
            $clientRequestId,
            $cancelReason,
            $transactionId,
        );
    }
}
