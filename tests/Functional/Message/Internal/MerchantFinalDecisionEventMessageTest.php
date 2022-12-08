<?php

declare(strict_types=1);

namespace App\Tests\Functional\Message\Internal;

use App\Message\Internal\MerchantFinalDecisionEventMessage;
use Happyr\MessageSerializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;

/**
 * @internal
 */
class MerchantFinalDecisionEventMessageTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testDecodeSuccess(): void
    {
        $serializer = self::getContainer()->get(Serializer::class);

        $result = $serializer->decode([
            'body' => '{"version":1,"identifier":"merchant-final-decision-event","timestamp":1636717394,"payload":{"decision":"Accepted", "transaction_id": 123, "user_id": 1, "timestamp": 1640693579, "gateway_data":{"test1": 11, "test2": "2"}},"_meta":[]}',
        ]);

        $message = $result->getMessage();
        self::assertInstanceOf(MerchantFinalDecisionEventMessage::class, $message);
        self::assertSame('Accepted', $message->getDecision()->getValue());
        self::assertSame(123, $message->getTransactionId()->getValue());
        self::assertSame(1, $message->getUserId()->getValue());
        self::assertSame(1640693579, $message->getTimestamp()->getValue());
        self::assertSame([
            'test1' => 11,
            'test2' => '2',
        ], $message->getGatewayData());
    }

    /**
     * @dataProvider provideInvalidMessageBodies
     */
    public function testDecodeFailure(string $invalidBody): void
    {
        $serializer = self::getContainer()->get(Serializer::class);
        $this->expectException(MessageDecodingFailedException::class);
        $serializer->decode([
            'body' => $invalidBody,
        ]);
    }

    public function provideInvalidMessageBodies(): iterable
    {
        return [
            'invalid_decision' => ['{"version":1,"identifier":"merchant-final-decision-event","timestamp":1636717394,"payload":{"decision":"fake", "transaction_id": 123, "user_id": 1, "timestamp": 1640693579, "gateway_data":{"test1": 11, "test2": "2"}},"_meta":[]}'],
            'missing_decision' => ['{"version":1,"identifier":"merchant-final-decision-event","timestamp":1636717394,"payload":{"transaction_id": 123, "user_id": 1, "timestamp": 1640693579, "gateway_data":{"test1": 11, "test2": "2"}},"_meta":[]}'],
            'missing_user_id' => ['{"version":1,"identifier":"merchant-final-decision-event","timestamp":1636717394,"payload":{"decision":"Accepted", "transaction_id": 123, "timestamp": 1640693579, "gateway_data":{"test1": 11, "test2": "2"}},"_meta":[]}'],
            'missing_transaction_id' => ['{"version":1,"identifier":"merchant-final-decision-event","timestamp":1636717394,"payload":{"decision":"Accepted", "user_id": 1, "timestamp": 1640693579, "gateway_data":{"test1": 11, "test2": "2"}},"_meta":[]}'],
            'missing_timestamp' => ['{"version":1,"identifier":"merchant-final-decision-event","timestamp":1636717394,"payload":{"decision":"Accepted", "transaction_id": 123, "user_id": 1, "gateway_data":{"test1": 11, "test2": "2"}},"_meta":[]}'],
        ];
    }
}
