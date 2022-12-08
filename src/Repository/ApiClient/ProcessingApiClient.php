<?php

declare(strict_types=1);

namespace App\Repository\ApiClient;

use App\DTO\TransactionCheckInfo;
use App\Exception\AccessDeniedException;
use App\Exception\ApiClientException;
use App\Exception\NotFoundException;
use App\Repository\ProcessingTransactionRepositoryInterface;
use App\VO\Amount;
use App\VO\Currency;
use App\VO\ExchangeRate;
use App\VO\Gateway;
use App\VO\Invoice;
use App\VO\Payment;
use App\VO\PaymentMethod\Alternative;
use App\VO\PaymentMethod\Alternative\Type;
use App\VO\PaymentMethod\Card;
use App\VO\PaymentMethod\Card\Bin;
use App\VO\PaymentMethod\Card\CardholderName;
use App\VO\PaymentMethod\Card\CountryCode;
use App\VO\PaymentMethod\Card\LastFourDigits;
use App\VO\PaymentMethod\Card\Scheme;
use App\VO\Payout;
use App\VO\TransactionId;
use Paybis\Processing\Api\Client;
use Paybis\Processing\Api\Exception\InternalGetTransactionCheckInfoForbiddenException;
use Paybis\Processing\Api\Exception\InternalGetTransactionCheckInfoNotFoundException;
use Paybis\Processing\Api\Model\TransactionCheckInfo as TransactionCheckInfoResponse;

class ProcessingApiClient implements ProcessingTransactionRepositoryInterface
{
    public function __construct(
        private Client $processingClient,
    ) {
    }

    /**
     * @throws AccessDeniedException
     * @throws NotFoundException
     */
    public function getTransactionCheckInfoByInvoice(Invoice $invoice): TransactionCheckInfo
    {
        try {
            /** @var TransactionCheckInfoResponse $transactionCheckInfoResponse */
            $transactionCheckInfoResponse = $this->processingClient->internalGetTransactionCheckInfo($invoice->getValue());
        } catch (InternalGetTransactionCheckInfoForbiddenException $e) {
            throw new AccessDeniedException($e->getMessage(), 'ACCESS_DENIED', $e);
        } catch (InternalGetTransactionCheckInfoNotFoundException $e) {
            throw new NotFoundException($e->getMessage(), 'NOT_FOUND', $e);
        } catch (\Throwable $e) {
            throw new ApiClientException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->prepareTransactionCheckInfo($transactionCheckInfoResponse);
    }

    private function prepareTransactionCheckInfo(TransactionCheckInfoResponse $transactionCheckInfoResponse): TransactionCheckInfo
    {
        return new TransactionCheckInfo(
            TransactionId::fromInt($transactionCheckInfoResponse->getId()),
            new Payment(
                Amount::fromString($transactionCheckInfoResponse->getPayment()->getAmount()),
                Currency::fromString($transactionCheckInfoResponse->getPayment()->getCurrency())
            ),
            new Payout(
                Amount::fromString($transactionCheckInfoResponse->getPayout()->getAmount()),
                Currency::fromString($transactionCheckInfoResponse->getPayout()->getCurrency()),
                ExchangeRate::fromString($transactionCheckInfoResponse->getPayout()->getExchangeRate()),
            ),
            (null !== $transactionCheckInfoResponse->getPayment()->getGateway())
                ? Gateway::fromString($transactionCheckInfoResponse->getPayment()->getGateway())
                : null,
            (null !== $transactionCheckInfoResponse->getPayment()->getCard())
                ? new Card(
                    Bin::fromString($transactionCheckInfoResponse->getPayment()->getCard()->getBin()),
                    LastFourDigits::fromString($transactionCheckInfoResponse->getPayment()->getCard()->getLastFourDigits()),
                    CardholderName::fromString($transactionCheckInfoResponse->getPayment()->getCard()->getCardholderName()),
                    (null !== $transactionCheckInfoResponse->getPayment()->getCard()->getCountryCode())
                    ? CountryCode::fromString($transactionCheckInfoResponse->getPayment()->getCard()->getCountryCode())
                    : null,
                    (null !== $transactionCheckInfoResponse->getPayment()->getCard()->getScheme())
                    ? Scheme::fromString($transactionCheckInfoResponse->getPayment()->getCard()->getScheme())
                    : null,
                    $transactionCheckInfoResponse->getPayment()->getCard()->getIsDebit()
                )
                : null,
            (null !== $transactionCheckInfoResponse->getPayment()->getAlternative())
                ? new Alternative(
                    Type::fromString($transactionCheckInfoResponse->getPayment()->getAlternative()->getType())
                )
                : null,
        );
    }
}
