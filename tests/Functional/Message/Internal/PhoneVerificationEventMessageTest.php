<?php

declare(strict_types=1);

namespace App\Tests\Functional\Message\Internal;

use App\Message\Internal\PhoneVerificationEventMessage;
use Happyr\MessageSerializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;

/**
 * @internal
 */
class PhoneVerificationEventMessageTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testDecodeSuccess(): void
    {
        $serializer = self::getContainer()->get(Serializer::class);

        $result = $serializer->decode([
            'body' => '{"version":1,"identifier":"phone-verification-event","timestamp":1640693579,
            "payload":{"user_id":810, "ip": "192.168.10.55", "timestamp": 1640693579, 
            "user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15",
             "device_id":"136016b3-e0bc-4c85-a453-545b112c5986","phone": "5211178455","country_code": "44"}, "_meta":[]}',
        ]);

        $message = $result->getMessage();

        self::assertInstanceOf(PhoneVerificationEventMessage::class, $message);
        self::assertSame(810, $message->getUserId()->getValue());
        self::assertSame('5211178455', $message->getPhone()->getValue());
        self::assertSame('44', $message->getCountryCode()->getValue());
        self::assertSame(1640693579, $message->getTimestamp()->getValue());
        self::assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15', $message->getUserAgent()->getValue());
        self::assertSame('136016b3-e0bc-4c85-a453-545b112c5986', $message->getDeviceId()->getValue());
        self::assertSame('192.168.10.55', $message->getIp()->getValue());
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
            'missing_user_id' => ['{"version":1,"identifier":"phoneVerification","timestamp":1640693579,"payload":{"email": "c.kent@example.com", "ip": "192.168.10.55","timestamp": 1640693579, "user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986"}, "_meta":[]}'],
            'missing_email' => ['{"version":1,"identifier":"phoneVerification","timestamp":1640693579, "payload":{"user_id":810, "ip": "192.168.10.55","timestamp": 1640693579, "user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986"}, "_meta":[]}'],
            'missing_timestamp' => ['{"version":1,"identifier":"phoneVerification","timestamp":1640693579,"payload":{"user_id":810, "email": "c.kent@example.com", "ip": "192.168.10.55","user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986"}, "_meta":[]}'],
            'missing_user_agent' => ['{"version":1,"identifier":"phoneVerification","timestamp":1640693579,"payload":{"user_id":810, "email": "c.kent@example.com", "ip": "192.168.10.55", "timestamp": 1640693579,"device_id":"136016b3-e0bc-4c85-a453-545b112c5986","event_id":"emailVerification"}, "_meta":[]}'],
            'missing_device_id' => ['{"version":1,"identifier":"phoneVerification","timestamp":1640693579,"payload":{"user_id":810, "email": "c.kent@example.com", "ip": "192.168.10.55","timestamp": 1640693579, "user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15"}, "_meta":[]}'],
            'missing_ip' => ['{"version":1,"identifier":"phoneVerification","timestamp":1640693579,"payload":{"user_id":810, "email": "c.kent@example.com","timestamp": 1640693579, "user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986"}, "_meta":[]}'],
        ];
    }
}