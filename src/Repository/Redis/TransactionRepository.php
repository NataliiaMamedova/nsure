<?php

declare(strict_types=1);

namespace App\Repository\Redis;

use App\Exception\TransactionNotFoundException;
use App\Repository\TransactionRepositoryInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(
        private CacheInterface $transactionPool,
        private int $transactionExpirationTime,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws TransactionNotFoundException
     */
    public function getInvoiceByTransactionId(int $transactionId): string
    {
        try {
            $invoice = $this->transactionPool->get((string) $transactionId, static function (ItemInterface $item): void {
            });
        } catch (InvalidArgumentException $e) {
            throw new TransactionNotFoundException();
        }

        if (null === $invoice) {
            $this->logger->info('invoice not found by transactionId: {transaction_id}', [
                'transaction_id' => $transactionId,
            ]);
            throw new TransactionNotFoundException();
        }

        return $invoice;
    }

    public function save(int $transactionId, string $invoice): void
    {
        $this->transactionPool->delete((string) $transactionId);
        $this->transactionPool->get((string) $transactionId, function (ItemInterface $item) use ($invoice): string {
            $item->expiresAfter($this->transactionExpirationTime);

            return $invoice;
        });
    }
}
