<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\TransactionCheckInfo;
use App\Exception\AccessDeniedException;
use App\Exception\NotFoundException;
use App\VO\Invoice;

interface ProcessingTransactionRepositoryInterface
{
    /**
     * @throws AccessDeniedException
     * @throws NotFoundException
     */
    public function getTransactionCheckInfoByInvoice(Invoice $invoice): TransactionCheckInfo;
}
