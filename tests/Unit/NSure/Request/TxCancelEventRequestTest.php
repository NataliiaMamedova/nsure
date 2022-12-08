<?php

declare(strict_types=1);

namespace App\Tests\Unit\NSure\Request;

use App\NSure\Request\TxCancelEventRequest;
use App\VO\CancelReason;
use App\VO\ClientRequestId;
use App\VO\EventId;
use App\VO\Timestamp;
use App\VO\TransactionId;
use App\VO\UserId;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class TxCancelEventRequestTest extends TestCase
{
    private const TRANSACTION_ID = 123;

    private const USER_ID = 1;

    private const EVENT_ID = 'txCancel';

    private const CANCEL_REASON = 'userFraud';

    private const TIMESTAMP = 1640693579;

    private const CLIENT_REQUEST_ID = 'ba4bb3cb-0da5-4885-afe8-85cd12f80b01';

    public function testSuccess(): void
    {
        $request = new TxCancelEventRequest(
            EventId::fromString(self::EVENT_ID),
            Timestamp::fromInt(self::TIMESTAMP),
            UserId::fromInt(self::USER_ID),
            ClientRequestId::fromString(self::CLIENT_REQUEST_ID),
            CancelReason::fromString(self::CANCEL_REASON),
            TransactionId::fromInt(self::TRANSACTION_ID),
        );

        $jsonContent = $request->makeBody();

        $body = \json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(self::EVENT_ID, $body['metadata']['type']);
        self::assertSame(self::TIMESTAMP, $body['metadata']['timestamp']);
        self::assertSame((string) self::USER_ID, $body['metadata']['clientUserId']);
        self::assertSame(self::CLIENT_REQUEST_ID, $body['metadata']['clientRequestId']);
        self::assertSame(self::CANCEL_REASON, $body['cancelInfo']['reason']);
        self::assertSame((string) self::TRANSACTION_ID, $body['cancelInfo']['txId']);
    }
}
