<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler\Internal;

use App\Message\Internal\MerchantFinalDecisionEventMessage;
use App\MessageHandler\Internal\MerchantFinalDecisionEventMessageHandler;
use App\VO\Decision;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserId;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class MerchantFinalDecisionEventMessageHandlerTest extends KernelTestCase
{
    private const DECISION_INTERNAL_ACCEPTED = 'Accepted';

    private const DECISION_NSURE_ACCEPTED = 'Accepted';

    private const TRANSACTION_ID = 123;

    private const USER_ID = 1;

    private const TIMESTAMP = 1640693579;

    private const GATEWAY_DATA = [
        'test' => 1,
    ];

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testMerchantFinalDecisionRequestSuccess(): void
    {
        /** @var Client $client */
        $client = self::getContainer()->get(Client::class);
        $client->addResponse(new Response(200, [], '{"ok": true}'));

        /** @var MerchantFinalDecisionEventMessageHandler $messageHandler */
        $messageHandler = self::getContainer()->get(MerchantFinalDecisionEventMessageHandler::class);
        $messageHandler($this->createMessage());

        $lastRequest = $client->getLastRequest();

        self::assertNotFalse($lastRequest, 'there\'s no sent request');

        $requestBody = $lastRequest->getBody();
        $requestBody->rewind();

        self::assertSame('/events', $lastRequest->getUri()->getPath());
        $requestData = \json_decode($requestBody->getContents(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('merchantFinalDecision', $requestData['metadata']['type']);
        self::assertSame(self::TIMESTAMP, $requestData['metadata']['timestamp']);
        self::assertSame((string) self::USER_ID, $requestData['metadata']['clientUserId']);
        self::assertNotEmpty($requestData['metadata']['clientRequestId']);
        self::assertSame(self::DECISION_NSURE_ACCEPTED, $requestData['merchantFinalDecision']['decision']);
        self::assertSame((string) self::TRANSACTION_ID, $requestData['merchantFinalDecision']['txId']);
        self::assertSame([
            'test' => 1,
        ], $requestData['merchantFinalDecision']['rawGatewayData']);
    }

    private function createMessage(): MerchantFinalDecisionEventMessage
    {
        return MerchantFinalDecisionEventMessage::create(
            Decision::fromString(self::DECISION_INTERNAL_ACCEPTED),
            TransactionId::fromInt(self::TRANSACTION_ID),
            UserId::fromInt(self::USER_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            self::GATEWAY_DATA
        );
    }
}
