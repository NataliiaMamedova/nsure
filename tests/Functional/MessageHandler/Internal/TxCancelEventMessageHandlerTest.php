<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler\Internal;

use App\Message\Internal\TxCancelEventMessage;
use App\MessageHandler\Internal\TxCancelEventMessageHandler;
use App\VO\CancelReason;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserId;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class TxCancelEventMessageHandlerTest extends KernelTestCase
{
    private const TRANSACTION_ID = 123;

    private const USER_ID = 1;

    private const EVENT_ID = 'txCancel';

    private const CANCEL_REASON = 'userFraud';

    private const TIMESTAMP = 1640693579;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testMerchantFinalDecisionRequestSuccess(): void
    {
        /** @var Client $client */
        $client = self::getContainer()->get(Client::class);
        $client->addResponse(new Response(200, [], '{"ok": true}'));

        /** @var TxCancelEventMessageHandler $messageHandler */
        $messageHandler = self::getContainer()->get(TxCancelEventMessageHandler::class);
        $messageHandler($this->createMessage());

        $lastRequest = $client->getLastRequest();

        self::assertNotFalse($lastRequest, 'there\'s no sent request');

        $requestBody = $lastRequest->getBody();
        $requestBody->rewind();

        self::assertSame('/events', $lastRequest->getUri()->getPath());
        $requestData = \json_decode($requestBody->getContents(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(self::EVENT_ID, $requestData['metadata']['type']);
        self::assertSame(self::TIMESTAMP, $requestData['metadata']['timestamp']);
        self::assertSame((string) self::USER_ID, $requestData['metadata']['clientUserId']);
        self::assertNotEmpty($requestData['metadata']['clientRequestId']);
        self::assertSame(self::CANCEL_REASON, $requestData['cancelInfo']['reason']);
        self::assertSame((string) self::TRANSACTION_ID, $requestData['cancelInfo']['txId']);
    }

    private function createMessage(): TxCancelEventMessage
    {
        return TxCancelEventMessage::create(
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            CancelReason::fromString(self::CANCEL_REASON),
            TransactionId::fromInt(self::TRANSACTION_ID),
        );
    }
}
