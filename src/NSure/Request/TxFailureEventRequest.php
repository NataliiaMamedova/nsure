<?php

declare(strict_types=1);

namespace App\NSure\Request;

use App\VO\ClientRequestId;
use App\VO\DeviceId;
use App\VO\Email;
use App\VO\EventId;
use App\VO\FailureReason;
use App\VO\FirstName;
use App\VO\Gateway;
use App\VO\IP;
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

class TxFailureEventRequest extends AbstractNSureRequest
{
    use SessionInfoTrait;

    private const DEFAULT_ALTERNATIVE_PAYMENT_METHOD = 'other';

    private const DEFAULT_FAILURE_REASON = 'merchantDeclined';

    public function __construct(
        private EventId $eventId,
        private Timestamp $timestamp,
        private UserId $userId,
        private ClientRequestId $clientRequestId,
        DeviceId $deviceId,
        UserAgent $userAgent,
        IP $ip,
        private Email $email,
        private PhoneNumber $phoneNumber,
        private PhoneCountryCode $phoneCountryCode,
        private FirstName $firstName,
        private LastName $lastName,
        private FailureReason $failureReason,
        private TransactionId $transactionId,
        private Payment $payment,
        private Payout $payout,
        private ?Gateway $gateway,
        private ?Card $card,
        private ?Alternative $alternative
    ) {
        parent::__construct($eventId, $timestamp, $userId, $clientRequestId);
        $this->deviceId = $deviceId;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
    }

    public function makeBody(): string
    {
        $userInfo = [
            'email' => $this->email->getValue(),
            'firstName' => $this->firstName->getValue(),
            'lastName' => $this->lastName->getValue(),
            'phoneInfo' => [
                'phone' => $this->phoneNumber->getValue(),
                'countryCode' => $this->phoneCountryCode->getValue(),
            ],
        ];

        $customEventData = [
            'personalInfo' => $userInfo,
            'recipientInfo' => $userInfo,
            'transactionDetails' => [
                'txId' => (string) $this->transactionId->getValue(),
                'paidAmount' => [
                    'valueInCurrency' => (float) $this->payment->getAmount()->getValue(),
                    'currency' => $this->payment->getCurrency()->getValue(),
                ],
                'paymentMethodDetails' => $this->getPaymentMethodDetails(),
                'cart' => $this->getCartData(),
                'failureReason' => $this->failureReason->getNsureValue(),
            ],
        ];

        return json_encode(array_merge($this->getMetaData(), $this->getSessionInfo(), $customEventData), JSON_THROW_ON_ERROR);
    }

    private function getPaymentMethodDetails(): array
    {
        $paymentMethodDetails = [];

        if (null !== $this->gateway) {
            $paymentMethodDetails['gateway'] = $this->gateway->getValue();
        }

        if (null !== $this->card) {
            $creditCardData = [
                'bin' => $this->card->getBin()->getValue(),
                'last4' => $this->card->getLastFourDigits()->getValue(),
                'cardHolderName' => $this->card->getCardholderName()->getValue(),
            ];
            if (null !== $this->card->getCountryCode()) {
                $creditCardData['countryCode'] = $this->card->getCountryCode()->getValue();
            }
            if (null !== $this->card->getScheme()) {
                $creditCardData['cardType'] = $this->card->getScheme()->getValue();
            }
            if (null !== $this->card->isDebit()) {
                $creditCardData['isDebit'] = $this->card->isDebit();
            }
            $paymentMethodDetails['creditCard'] = $creditCardData;
        } else {
            $paymentMethodDetails['alternative'] = [
                'type' => (null !== $this->alternative)
                    ? $this->alternative->getType()->getValue()
                    : self::DEFAULT_ALTERNATIVE_PAYMENT_METHOD,
            ];
        }

        return $paymentMethodDetails;
    }

    private function getCartData(): array
    {
        return [
            [
                'brand' => $this->payout->getCurrency()->getValue(),
                'quantity' => (float) $this->payout->getAmount()->getValue(),
                'itemFulfillment' => 'digital',
                'sellingPrice' => [
                    'valueInCurrency' => (float) $this->payout->getExchangeRate()->getValue(),
                    'currency' => $this->payment->getCurrency()->getValue(),
                ],
            ],
        ];
    }
}
