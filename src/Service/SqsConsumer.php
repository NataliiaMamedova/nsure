<?php

declare(strict_types=1);

namespace App\Service;

use Bref\Context\Context;
use Bref\Event\Sqs\SqsEvent;
use Bref\Event\Sqs\SqsHandler;
use Bref\Symfony\Messenger\Service\BusDriver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class SqsConsumer extends SqsHandler
{
    protected SerializerInterface $serializer;

    private MessageBusInterface $bus;

    private string $transportName;

    private BusDriver $busDriver;

    private LoggerInterface $logger;

    public function __construct(
        BusDriver $busDriver,
        MessageBusInterface $bus,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $transportName
    ) {
        $this->busDriver = $busDriver;
        $this->bus = $bus;
        $this->serializer = $serializer;
        $this->transportName = $transportName;
        $this->logger = $logger;
    }

    public function handleSqs(SqsEvent $event, Context $context): void
    {
        try {
            foreach ($event->getRecords() as $record) {
                $attributes = $record->getMessageAttributes();
                $headers = $attributes['Headers']['stringValue'] ?? '[]';
                $envelope = $this->serializer->decode([
                    'body' => $record->getBody(),
                    'headers' => json_decode($headers, true),
                ]);

                $this->busDriver->putEnvelopeOnBus($this->bus, $envelope, $this->transportName);
            }
        } catch (\Throwable $e) {
            $this->logger->error((string) $e, [
                'aws_context' => $context->jsonSerialize(),
                'event' => $event->toArray(),
            ]);
        }
    }
}
