<?php

declare(strict_types=1);

namespace App\MessageHandler\Internal;

use App\Exception\NSureServiceException;
use App\Message\Internal\TxCancelEventMessage;
use App\NSure\Service\NSureService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TxCancelEventMessageHandler implements MessageHandlerInterface
{
    private NSureService $nSureService;

    public function __construct(NSureService $nSureService)
    {
        $this->nSureService = $nSureService;
    }

    /**
     * @throws NSureServiceException
     */
    public function __invoke(TxCancelEventMessage $message): void
    {
        $this->nSureService->processTxCancel(
            $message->getUserId(),
            $message->getTimestamp(),
            $message->getCancelReason(),
            $message->getTransactionId(),
        );
    }
}
