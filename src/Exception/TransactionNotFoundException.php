<?php

declare(strict_types=1);

namespace App\Exception;

class TransactionNotFoundException extends AbstractException
{
    public function __construct()
    {
        parent::__construct('Transaction not found', 'TRANSACTION_NOT_FOUND');
    }
}
