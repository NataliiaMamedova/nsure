<?php

declare(strict_types=1);

namespace App\NSure\Service;

use App\Exception\NSureClientException;
use App\Exception\NSureImplementationException;
use App\Exception\NSureServiceException;
use App\NSure\Factory\EventRequestFactory;
use App\NSure\NSureClient;
use App\NSure\Request\EmailVerificationEventRequest;
use App\NSure\Request\MerchantFinalDecisionEventRequest;
use App\NSure\Request\NSureRequestInterface;
use App\NSure\Request\PaymentMethodEventRequest;
use App\NSure\Request\PhoneVerificationEventRequest;
use App\NSure\Request\RecipientUpdateEventRequest;
use App\NSure\Request\SignInEventRequest;
use App\NSure\Request\SignOutEventRequest;
use App\NSure\Request\SignUpEventRequest;
use App\NSure\Request\TxCancelEventRequest;
use App\NSure\Request\TxFailureEventRequest;
use App\Repository\ProcessingTransactionRepositoryInterface;
use App\VO\Bin;
use App\VO\CancelReason;
use App\VO\CardholderName;
use App\VO\CardType;
use App\VO\Decision;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\FailureReason;
use App\VO\FirstName;
use App\VO\Invoice;
use App\VO\IP;
use App\VO\Last4;
use App\VO\LastName;
use App\VO\PhoneCountryCode;
use App\VO\PhoneNumber;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserAgent;
use App\VO\UserId;
use Psr\Log\LoggerInterface;

class NSureService
{
    public function __construct(
        private NSureClient $nSureClient,
        private EventRequestFactory $eventRequestFactory,
        private ProcessingTransactionRepositoryInterface $processingTransactionRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws NSureServiceException|\JsonException
     */
    public function processPhoneVerificationEvent(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode
    ): void {
        $this->sendRequestWithExceptionProcessing(
            function (PhoneVerificationEventRequest $request): void {
                $this->nSureClient->sendEventPhoneVerification($request);
            },
            $this->eventRequestFactory->createPhoneVerificationEventRequest(
                $timestamp,
                $userId,
                $deviceId,
                $userAgent,
                $ip,
                $phoneNumber,
                $phoneCountryCode
            )
        );
    }

    /**
     * @throws NSureServiceException|\JsonException
     */
    public function processEmailVerificationEvent(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Email $email
    ): void {
        $this->sendRequestWithExceptionProcessing(
            function (EmailVerificationEventRequest $request): void {
                $this->nSureClient->sendEventEmailVerification($request);
            },
            $this->eventRequestFactory->createEmailVerificationEventRequest(
                $timestamp,
                $userId,
                $deviceId,
                $userAgent,
                $ip,
                $email
            )
        );
    }

    /**
     * @throws NSureServiceException|\JsonException
     */
    public function processMerchantFinalDecisionEvent(
        Decision $decision,
        TransactionId $transactionId,
        UserId $userId,
        Timestamp $timestamp,
        array $gatewayData = [],
    ): void {
        $this->sendRequestWithExceptionProcessing(
            function (MerchantFinalDecisionEventRequest $request): void {
                $this->nSureClient->sendMerchantFinalDecision($request);
            },
            $this->eventRequestFactory->createMerchantFinalDecisionRequest(
                $decision,
                $transactionId,
                $userId,
                $timestamp,
                $gatewayData,
            )
        );
    }

    /**
     * @throws NSureServiceException|\JsonException
     */
    public function processSignUpEvent(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        Email $email,
        PhoneNumber $phoneNumber,
        PhoneCountryCode $phoneCountryCode
    ): void {
        $this->sendRequestWithExceptionProcessing(
            function (SignUpEventRequest $request): void {
                $this->nSureClient->sendSignUpEvent($request);
            },
            $this->eventRequestFactory->createSignUpEventRequest(
                $timestamp,
                $userId,
                $deviceId,
                $userAgent,
                $ip,
                $email,
                $phoneNumber,
                $phoneCountryCode
            )
        );
    }

    /**
     * @throws NSureServiceException|\JsonException
     */
    public function processSignInEvent(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
    ): void {
        $this->sendRequestWithExceptionProcessing(
            function (SignInEventRequest $request): void {
                $this->nSureClient->sendSignInEvent($request);
            },
            $this->eventRequestFactory->createSignInEventRequest(
                $timestamp,
                $userId,
                $deviceId,
                $userAgent,
                $ip
            )
        );
    }

    public function processTxCancel(
        UserId $userId,
        Timestamp $timestamp,
        CancelReason $cancelReason,
        TransactionId $transactionId,
    ): void {
        $this->sendRequestWithExceptionProcessing(
            function (TxCancelEventRequest $request): void {
                $this->nSureClient->sendTxCancelEvent($request);
            },
            $this->eventRequestFactory->createTxCancelRequest(
                $timestamp,
                $userId,
                $cancelReason,
                $transactionId,
            )
        );
    }

    /**
     * @throws NSureServiceException|\JsonException
     */
    public function processRecipientUpdateEvent(
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
    ): void {
        $this->sendRequestWithExceptionProcessing(
            function (RecipientUpdateEventRequest $request): void {
                $this->nSureClient->sendRecipientUpdateEvent($request);
            },
            $this->eventRequestFactory->createRecipientUpdateEventRequest(
                $timestamp,
                $userId,
                $deviceId,
                $userAgent,
                $ip,
                $email,
                $firstName,
                $lastName,
                $phoneNumber,
                $phoneCountryCode,
                $transactionId,
            )
        );
    }

    /**
     * @throws NSureServiceException|\JsonException
     */
    public function processSignOutEvent(
        Timestamp $timestamp,
        UserId $userId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
    ): void {
        $this->sendRequestWithExceptionProcessing(
            function (SignOutEventRequest $request): void {
                $this->nSureClient->sendSignOutEvent($request);
            },
            $this->eventRequestFactory->createSignOutEventRequest(
                $timestamp,
                $userId,
                $deviceId,
                $userAgent,
                $ip
            )
        );
    }

    /**
     * @throws NSureServiceException|\JsonException
     */
    public function processTxFailure(
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
        Invoice $invoice,
        FailureReason $failureReason,
    ): void {
        $transactionData = $this->processingTransactionRepository->getTransactionCheckInfoByInvoice($invoice);

        $request = $this->eventRequestFactory->createTxFailureEventRequest(
            $timestamp,
            $userId,
            $deviceId,
            $userAgent,
            $ip,
            $email,
            $firstName,
            $lastName,
            $phoneNumber,
            $phoneCountryCode,
            $failureReason,
            $transactionData->getTransactionId(),
            $transactionData->getPayment(),
            $transactionData->getPayout(),
            $transactionData->getGateway(),
            $transactionData->getCard(),
            $transactionData->getAlternative(),
        );

        $this->sendRequestWithExceptionProcessing(
            function (TxFailureEventRequest $request): void {
                $this->nSureClient->sendTxFailureEvent($request);
            },
            $request
        );
    }

    /**
     * @throws NSureServiceException
     */
    public function processPaymentMethodEvent(
        UserId $userId,
        Timestamp $timestamp,
        Last4 $last4,
        Bin $bin,
        CardholderName $cardholderName,
        CardType $cardType,
        bool $isDebit,
        IP $ip,
        UserAgent $userAgent,
        DeviceId $deviceId
    ): void {
        $this->sendRequestWithExceptionProcessing(
            function (PaymentMethodEventRequest $request): void {
                $this->nSureClient->sendPaymentMethodEvent($request);
            },
            $this->eventRequestFactory->createPaymentMethodEventRequest(
                $timestamp,
                $userId,
                $deviceId,
                $userAgent,
                $ip,
                $bin,
                $cardType,
                $cardholderName,
                $isDebit,
                $last4
            )
        );
    }

    /**
     * @throws NSureServiceException
     */
    private function sendRequestWithExceptionProcessing(callable $sendRequest, NSureRequestInterface $request): void
    {
        try {
            $sendRequest($request);
        } catch (NSureImplementationException $e) {
            $this->logger->error(
                'implementation error: {message}',
                [
                    'request_body' => json_decode($request->makeBody(), true, 512, JSON_THROW_ON_ERROR),
                    'message' => $e->getMessage(),
                ]
            );
            throw NSureServiceException::implementationError($e);
        } catch (NSureClientException $e) {
            $this->logger->error(
                'client error: {message}',
                [
                    'request_body' => json_decode($request->makeBody(), true, 512, JSON_THROW_ON_ERROR),
                    'message' => $e->getMessage(),
                ]
            );
            throw NSureServiceException::clientError($e);
        }
    }
}
