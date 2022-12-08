<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\TransactionNotFoundException;

interface TransactionRepositoryInterface
{
    /**
     * @throws TransactionNotFoundException
     */
    public function getInvoiceByTransactionId(int $transactionId): string;

    public function save(int $transactionId, string $invoice): void;
}
