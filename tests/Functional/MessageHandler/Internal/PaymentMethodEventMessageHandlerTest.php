<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler\Internal;

use App\Message\Internal\PaymentMethodEventMessage;
use App\MessageHandler\Internal\PaymentMethodEventMessageHandler;
use App\VO\Bin;
use App\VO\CardCountryCode;
use App\VO\CardholderName;
use App\VO\CardId;
use App\VO\CardType;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Last4;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class PaymentMethodEventMessageHandlerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testMerchantFinalDecisionRequestSuccess(): void
    {
        /** @var Client $client */
        $client = self::getContainer()->get(Client::class);
        $client->addResponse(new Response(200, [], '{"ok": true}'));

        /** @var PaymentMethodEventMessageHandler $messageHandler */
        $messageHandler = self::getContainer()->get(PaymentMethodEventMessageHandler::class);
        $messageHandler($this->createMessage());

        $lastRequest = $client->getLastRequest();

        self::assertNotFalse($lastRequest, 'there\'s no sent request');

        $requestBody = $lastRequest->getBody();
        $requestBody->rewind();

        self::assertSame('/events', $lastRequest->getUri()->getPath());
        $requestData = \json_decode($requestBody->getContents(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('2134', $requestData['paymentMethodDetails']['creditCard']['last4']);
        self::assertSame('516811', $requestData['paymentMethodDetails']['creditCard']['bin']);
        self::assertSame('Vasya Pupkin', $requestData['paymentMethodDetails']['creditCard']['cardHolderName']);
        self::assertSame('credit', $requestData['paymentMethodDetails']['creditCard']['cardType']);
        self::assertFalse($requestData['paymentMethodDetails']['creditCard']['isDebit']);
        self::assertSame('192.168.1.1', $requestData['sessionInfo']['ip']);
        self::assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15', $requestData['sessionInfo']['userAgent']);
        self::assertSame('f06e1b6c-e7ba-4ae4-85f8-a7f3a89919f3', $requestData['sessionInfo']['deviceId']);
        self::assertSame('paymentMethod', $requestData['metadata']['type']);
    }

    private function createMessage(): PaymentMethodEventMessage
    {
        return PaymentMethodEventMessage::create(
            UserId::fromInt(810),
            CardId::fromString('ddcbc8a1-80aa-4b5f-86a2-f6637dfc451a'),
            Timestamp::fromInt(time()),
            Last4::fromString('2134'),
            Bin::fromString('516811'),
            CardholderName::fromString('Vasya Pupkin'),
            CardType::fromString('credit'),
            false,
            CardCountryCode::fromString('UA'),
            IP::fromString('192.168.1.1'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1 Safari/605.1.15'),
            DeviceId::fromString('f06e1b6c-e7ba-4ae4-85f8-a7f3a89919f3')
        );
    }
}
