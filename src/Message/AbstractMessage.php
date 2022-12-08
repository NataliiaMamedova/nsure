<?php

declare(strict_types=1);

namespace App\Message;

use Happyr\MessageSerializer\Transformer\TransformerInterface;
use Symfony\Component\Messenger\Envelope;

abstract class AbstractMessage implements TransformerInterface, MessageInterface, \JsonSerializable
{
    /**
     * Constructor must be public and empty.
     */
    final public function __construct()
    {
    }

    /**
     * @param object $message
     */
    public function supportsTransform($message): bool
    {
        if ($message instanceof Envelope) {
            $message = $message->getMessage();
        }

        return $message instanceof static;
    }

    /**
     * @param object $message
     */
    public function getPayload($message): array
    {
        if ($message instanceof Envelope) {
            $message = $message->getMessage();
        }

        /** @var self $message */
        return $message->jsonSerialize();
    }

    public function supportsHydrate(string $identifier, int $version): bool
    {
        return $identifier === $this->getIdentifier() && $this->getVersion() === $version;
    }

    public function jsonSerialize()
    {
        return [
            'timestamp' => time(),
        ];
    }
}
