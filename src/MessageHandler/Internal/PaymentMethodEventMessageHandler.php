<?php

declare(strict_types=1);

namespace App\MessageHandler\Internal;

use App\Message\Internal\PaymentMethodEventMessage;
use App\MessageHandler\MessageValidatorTrait;
use App\NSure\Service\NSureService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentMethodEventMessageHandler implements MessageHandlerInterface
{
    use MessageValidatorTrait;

    private NSureService $nSureService;

    public function __construct(NSureService $nSureService, ValidatorInterface $validator)
    {
        $this->nSureService = $nSureService;
        $this->validator = $validator;
    }

    public function __invoke(PaymentMethodEventMessage $message): void
    {
        $this->validate($message);

        $this->nSureService->processPaymentMethodEvent(
            $message->getUserId(),
            $message->getTimestamp(),
            $message->getLast4(),
            $message->getBin(),
            $message->getCardholderName(),
            $message->getCardType(),
            $message->isDebit(),
            $message->getIp(),
            $message->getUserAgent(),
            $message->getDeviceId()
        );
    }
}
