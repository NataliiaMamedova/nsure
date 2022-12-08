<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository\ApiClient;

use App\Exception\AccessDeniedException;
use App\Exception\NotFoundException;
use App\Repository\ApiClient\ProcessingApiClient;
use App\VO\Invoice;
use Paybis\Processing\Api\Client;
use Paybis\Processing\Api\Exception\InternalGetTransactionCheckInfoForbiddenException;
use Paybis\Processing\Api\Exception\InternalGetTransactionCheckInfoNotFoundException;
use Paybis\Processing\Api\Model\TransactionCheckInfo as TransactionCheckInfoResponse;
use Paybis\Processing\Api\Model\TransactionCheckInfoPayment;
use Paybis\Processing\Api\Model\TransactionCheckInfoPaymentAlternative;
use Paybis\Processing\Api\Model\TransactionCheckInfoPaymentCard;
use Paybis\Processing\Api\Model\TransactionCheckInfoPayout;
use PHPStan\Testing\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
class ProcessingApiClientTest extends TestCase
{
    private const INVOICE = 'invoice';

    private const TRANSACTION_ID = 123;

    private const PAYMENT_AMOUNT = '351.75';

    private const PAYMENT_CURRENCY = 'USD';

    private const PAYOUT_AMOUNT = '3.5';

    private const PAYOUT_CURRENCY = 'BTC';

    private const EXCHANGE_RATE = '100.5';

    private const GATEWAY = 'worldpay';

    private const BIN = '123456';

    private const LAST4 = '6789';

    private const SCHEME = 'visa';

    private const CARDHOLDER_NAME = 'first last';

    private const IS_DEBIT = true;

    private const CARD_COUNTRY = 'US';

    private const ALTERNATIVE_TYPE = 'bankTransfer';

    /**
     * @var MockObject|Client
     */
    private ?MockObject $processingClientMock;

    private ?ProcessingApiClient $apiClient;

    protected function setUp(): void
    {
        $this->processingClientMock = $this->createMock(Client::class);
        $this->apiClient = new ProcessingApiClient($this->processingClientMock);
    }

    protected function tearDown(): void
    {
        $this->processingClientMock = null;
        $this->apiClient = null;
    }

    public function testGetTransactionCheckInfoByInvoiceSuccessWithAllData(): void
    {
        $clientResponse = $this->getTransactionCheckInfoResponse();

        $this->processingClientMock
            ->expects($this->once())
            ->method('internalGetTransactionCheckInfo')
            ->willReturn($clientResponse)
        ;

        $transactionCheckInfoDTO = $this->apiClient->getTransactionCheckInfoByInvoice(Invoice::fromString(self::INVOICE));

        self::assertSame(self::TRANSACTION_ID, $transactionCheckInfoDTO->getTransactionId()->getValue());
        self::assertSame(self::GATEWAY, $transactionCheckInfoDTO->getGateway()->getValue());
        self::assertSame(self::PAYMENT_AMOUNT, $transactionCheckInfoDTO->getPayment()->getAmount()->getValue());
        self::assertSame(self::PAYMENT_CURRENCY, $transactionCheckInfoDTO->getPayment()->getCurrency()->getValue());
        self::assertSame(self::PAYOUT_AMOUNT, $transactionCheckInfoDTO->getPayout()->getAmount()->getValue());
        self::assertSame(self::PAYOUT_CURRENCY, $transactionCheckInfoDTO->getPayout()->getCurrency()->getValue());
        self::assertSame(self::EXCHANGE_RATE, $transactionCheckInfoDTO->getPayout()->getExchangeRate()->getValue());
        self::assertSame(self::BIN, $transactionCheckInfoDTO->getCard()->getBin()->getValue());
        self::assertSame(self::LAST4, $transactionCheckInfoDTO->getCard()->getLastFourDigits()->getValue());
        self::assertSame(self::CARDHOLDER_NAME, $transactionCheckInfoDTO->getCard()->getCardholderName()->getValue());
        self::assertSame(self::SCHEME, $transactionCheckInfoDTO->getCard()->getScheme()->getValue());
        self::assertSame(self::CARD_COUNTRY, $transactionCheckInfoDTO->getCard()->getCountryCode()->getValue());
        self::assertSame(self::IS_DEBIT, $transactionCheckInfoDTO->getCard()->isDebit());
        self::assertSame(self::ALTERNATIVE_TYPE, $transactionCheckInfoDTO->getAlternative()->getType()->getValue());
    }

    public function testNotFoundException(): void
    {
        $this->processingClientMock
            ->expects($this->once())
            ->method('internalGetTransactionCheckInfo')
            ->willThrowException(new InternalGetTransactionCheckInfoNotFoundException())
        ;

        $this->expectException(NotFoundException::class);

        $this->apiClient->getTransactionCheckInfoByInvoice(Invoice::fromString(self::INVOICE));
    }

    public function testForbiddenException(): void
    {
        $this->processingClientMock
            ->expects($this->once())
            ->method('internalGetTransactionCheckInfo')
            ->willThrowException(new InternalGetTransactionCheckInfoForbiddenException())
        ;

        $this->expectException(AccessDeniedException::class);

        $this->apiClient->getTransactionCheckInfoByInvoice(Invoice::fromString(self::INVOICE));
    }

    public function testRuntimeException(): void
    {
        $this->processingClientMock
            ->expects($this->once())
            ->method('internalGetTransactionCheckInfo')
            ->willThrowException(new \Exception('test'))
        ;

        $this->expectException(\RuntimeException::class);

        $this->apiClient->getTransactionCheckInfoByInvoice(Invoice::fromString(self::INVOICE));
    }

    private function getTransactionCheckInfoResponse(): TransactionCheckInfoResponse
    {
        $transactionCheckInfo = new TransactionCheckInfoResponse();
        $transactionCheckInfo->setId(self::TRANSACTION_ID);
        $payment = new TransactionCheckInfoPayment();
        $payment->setAmount(self::PAYMENT_AMOUNT);
        $payment->setCurrency(self::PAYMENT_CURRENCY);
        $payment->setGateway(self::GATEWAY);
        $card = new TransactionCheckInfoPaymentCard();
        $card->setBin(self::BIN);
        $card->setCardholderName(self::CARDHOLDER_NAME);
        $card->setCountryCode(self::CARD_COUNTRY);
        $card->setIsDebit(self::IS_DEBIT);
        $card->setLastFourDigits(self::LAST4);
        $card->setScheme(self::SCHEME);
        $payment->setCard($card);
        $alternativePaymentMethod = new TransactionCheckInfoPaymentAlternative();
        $alternativePaymentMethod->setType(self::ALTERNATIVE_TYPE);
        $payment->setAlternative($alternativePaymentMethod);
        $transactionCheckInfo->setPayment($payment);
        $payout = new TransactionCheckInfoPayout();
        $payout->setAmount(self::PAYOUT_AMOUNT);
        $payout->setCurrency(self::PAYOUT_CURRENCY);
        $payout->setExchangeRate(self::EXCHANGE_RATE);
        $transactionCheckInfo->setPayout($payout);

        return $transactionCheckInfo;
    }
}
