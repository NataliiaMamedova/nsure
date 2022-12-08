<?php

declare(strict_types=1);

namespace App\Tests\Functional\Message\Internal;

use App\Message\Internal\PaymentMethodEventMessage;
use Happyr\MessageSerializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;

/**
 * @internal
 */
class PaymentMethodEventMessageTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testDecodeSuccess(): void
    {
        $serializer = self::getContainer()->get(Serializer::class);

        $result = $serializer->decode([
            'body' => file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/payment-method-event-success.json'),
        ]);

        $message = $result->getMessage();
        self::assertInstanceOf(PaymentMethodEventMessage::class, $message);
        self::assertSame('2134', $message->getLast4()->getValue());
        self::assertSame('516811', $message->getBin()->getValue());
        self::assertSame(810, $message->getUserId()->getValue());
        self::assertSame('ddcbc8a1-80aa-4b5f-86a2-f6637dfc451a', $message->getCardId()->getValue());
        self::assertSame(1640693579, $message->getTimestamp()->getValue());
        self::assertSame('Vasya Pupkin', $message->getCardholderName()->getValue());
        self::assertSame('credit', $message->getCardType()->getValue());
        self::assertFalse($message->isDebit());
        self::assertSame('UA', $message->getCountryCode()->getValue());
        self::assertSame('192.168.1.1', $message->getIp()->getValue());
        self::assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15', $message->getUserAgent()->getValue());
        self::assertSame('f06e1b6c-e7ba-4ae4-85f8-a7f3a89919f3', $message->getDeviceId()->getValue());
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
            'missing_bin' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-bin.json')],
            'missing_card_id' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-card-id.json')],
            'missing_card_type' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-card-type.json')],
            'missing_cardholder_name' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-cardholder-name.json')],
            'missing_country_code' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-country-code.json')],
            'missing_ip' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-ip.json')],
            'missing_is_debit' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-is-debit.json')],
            'missing_last4' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-last4.json')],
            'missing_timestamp' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-timestamp.json')],
            'missing_user_id' => [file_get_contents(__DIR__ . '/fixtures/PaymentMethodEventMessage/invalid/payment-method-event-missing-user-id.json')],
        ];
    }
}
