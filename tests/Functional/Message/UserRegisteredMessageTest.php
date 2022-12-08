<?php

declare(strict_types=1);

namespace App\Tests\Functional\Message;

use App\Message\UserRegisteredMessage;
use Happyr\MessageSerializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;

/**
 * @internal
 */
class UserRegisteredMessageTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testDecodeSuccess(): void
    {
        $serializer = self::getContainer()->get(Serializer::class);

        $result = $serializer->decode([
            'body' => '{"version":1,"identifier":"user-registered","timestamp":1640693579,
            "payload":{"user_id":801, "email": "c.kent@example.com", 
            "timestamp": 1640693579, "phone":"7521178454","country_code":"44",
            "metadata":{"user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15",
            "device_id":"136016b3-e0bc-4c85-a453-545b112c5986","ip": "192.168.10.55"}}, "_meta":[]}',
        ]);

        $message = $result->getMessage();

        self::assertInstanceOf(UserRegisteredMessage::class, $message);
        self::assertSame(801, $message->getUserId()->getValue());
        self::assertSame('c.kent@example.com', $message->getEmail()->getValue());
        self::assertSame(1640693579, $message->getTimestamp()->getValue());
        self::assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15', $message->getUserAgent()->getValue());
        self::assertSame('136016b3-e0bc-4c85-a453-545b112c5986', $message->getDeviceId()->getValue());
        self::assertSame('192.168.10.55', $message->getIp()->getValue());
        self::assertSame('7521178454', $message->getPhoneNumber()->getValue());
        self::assertSame('44', $message->getCountryCode()->getValue());
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
            'missing_user_id' => ['{"version":1,"identifier":"user-registered","timestamp":1640693579, "payload":{ "email": "c.kent@example.com","timestamp": 1640693579, "phone":"7521178454","country_code":"44", "metadata":{"user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986","ip": "192.168.10.55"}}, "_meta":[]}'],
            'missing_email' => ['{"version":1,"identifier":"user-registered","timestamp":1640693579,"payload":{"user_id":801,"timestamp": 1640693579, "phone":"7521178454","country_code":"44","metadata":{"user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986","ip": "192.168.10.55"}}, "_meta":[]}'],
            'missing_timestamp' => ['{"version":1,"identifier":"user-registered","timestamp":1640693579, "payload":{"user_id":801, "email": "c.kent@example.com","phone":"7521178454","country_code":"44","metadata":{"user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986","ip": "192.168.10.55"}}, "_meta":[]}'],
            'missing_user_agent' => ['{"version":1,"identifier":"user-registered","timestamp":1640693579,"payload":{"user_id":801, "email": "c.kent@example.com", "timestamp": 1640693579, "phone":"7521178454","country_code":"44","metadata":{"device_id":"136016b3-e0bc-4c85-a453-545b112c5986","ip": "192.168.10.55"}}, "_meta":[]}'],
            'missing_device_id' => ['{"version":1,"identifier":"user-registered","timestamp":1640693579, "payload":{"user_id":801,"email": "c.kent@example.com","timestamp": 1640693579, "phone":"7521178454","country_code":"44", "metadata":{"user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","ip": "192.168.10.55"}}, "_meta":[]}'],
            'missing_ip' => ['{"version":1,"identifier":"user-registered","timestamp":1640693579, "payload":{ "email": "c.kent@example.com","timestamp": 1640693579, "phone":"7521178454","country_code":"44", "metadata":{"user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986"}}, "_meta":[]}'],
            'missing_phone' => ['{"version":1,"identifier":"user-registered","timestamp":1640693579, "payload":{ "email": "c.kent@example.com","timestamp": 1640693579,"country_code":"44", "metadata":{"user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986","ip": "192.168.10.55"}}, "_meta":[]}'],
            'missing_country_code' => ['{"version":1,"identifier":"user-registered","timestamp":1640693579, "payload":{ "email": "c.kent@example.com","timestamp": 1640693579, "phone":"7521178454","metadata":{"user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15","device_id":"136016b3-e0bc-4c85-a453-545b112c5986","ip": "192.168.10.55"}}, "_meta":[]}'],
        ];
    }
}
