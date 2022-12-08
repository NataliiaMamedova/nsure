<?php

declare(strict_types=1);

namespace App\DTO;

use App\VO\Gateway;
use App\VO\Payment;
use App\VO\PaymentMethod\Alternative;
use App\VO\PaymentMethod\Card;
use App\VO\Payout;
use App\VO\TransactionId;

class TransactionCheckInfo
{
    public function __construct(
        private TransactionId $transactionId,
        private Payment $payment,
        private Payout $payout,
        private ?Gateway $gateway,
        private ?Card $card,
        private ?Alternative $alternative,
    ) {
    }

    public function getTransactionId(): TransactionId
    {
        return $this->transactionId;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    public function getGateway(): ?Gateway
    {
        return $this->gateway;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function getAlternative(): ?Alternative
    {
        return $this->alternative;
    }

    public function getPayout(): Payout
    {
        return $this->payout;
    }
}
