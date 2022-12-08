<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository\DynamoDB;

use App\Entity\SessionInfo;
use App\Exception\SessionInfoNotFoundException;
use App\Exception\SessionInfoRepositoryException;
use App\Repository\DynamoDb\SessionInfoRepository;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Aws\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class SessionInfoRepositoryTest extends TestCase
{
    private const TABLE_NAME = 'tableName';

    private const TIMESTAMP = 1640693579;

    private SessionInfoRepository $repository;

    private MockObject|DynamoDbClient $client;

    private LoggerInterface|MockObject $logger;

    private SessionInfo $sessionInfo;

    private array $itemResult;

    private Marshaler $marshaler;

    protected function setUp(): void
    {
        $this->client = $this->createMock(DynamoDbClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->marshaler = new Marshaler();
        $this->repository = new SessionInfoRepository(self::TABLE_NAME, $this->client, $this->marshaler, $this->logger);
        $this->sessionInfo = new SessionInfo(
            UserId::fromInt(1),
            Timestamp::fromInt(self::TIMESTAMP),
            DeviceId::fromString('u123'),
            UserAgent::fromString('Mozilla/5.0 (Macintosh;)'),
            IP::fromString('210.30.218.219'),
        );

        $this->itemResult = [
            'latest_device_id' => [
                'S' => $this->sessionInfo->getLatestDeviceId()->getValue(),
            ],
            'latest_user_agent' => [
                'S' => $this->sessionInfo->getLatestUserAgent()->getValue(),
            ],
            'latest_ip' => [
                'S' => $this->sessionInfo->getLatestIp()->getValue(),
            ],
            'updated_at' => [
                'N' => '1483229087145',
            ],
            'user_id' => [
                'N' => $this->sessionInfo->getUserId()->getValue(),
            ],
        ];
    }

    public function testSaveSuccess(): void
    {
        $this->client->expects(static::once())->method('__call')
            ->with('putItem', static::anything())
            ->willReturn(new Result())
        ;
        $this->repository->save($this->sessionInfo);
    }

    public function testSaveException(): void
    {
        $this->client->method('__call')
            ->with('putItem', static::anything())
            ->willThrowException($this->createMock(DynamoDbException::class))
        ;
        static::expectException(SessionInfoRepositoryException::class);

        $this->repository->save($this->sessionInfo);
    }

    public function testGetByUserId(): void
    {
        $this->client->method('__call')
            ->with('getItem', [
                0 => [
                    'TableName' => self::TABLE_NAME,
                    'Key' => $this->marshaler->marshalJson(json_encode([
                        'user_id' => $this->sessionInfo->getUserId()->getValue(),
                    ], JSON_THROW_ON_ERROR)),
                ],
            ])
            ->willReturn(new Result([
                'Item' => [$this->itemResult],
            ]))
        ;

        $result = $this->repository->getByUserId($this->sessionInfo->getUserId());

        $this->assertSessionInfoResult($result);
    }

    public function testGetByUserIdSessionInfoRepositoryException(): void
    {
        $this->client->method('__call')
            ->with('getItem', static::isType('array'))
            ->willThrowException($this->createMock(DynamoDbException::class))
        ;
        static::expectException(SessionInfoRepositoryException::class);
        $this->repository->getByUserId($this->sessionInfo->getUserId());
    }

    public function testGetByUserIdSessionInfoNotFoundException(): void
    {
        $this->client->method('__call')
            ->with('getItem', static::isType('array'))
            ->willReturn(new Result([
                'Item' => [],
            ]))
        ;
        static::expectException(SessionInfoNotFoundException::class);
        $this->repository->getByUserId($this->sessionInfo->getUserId());
    }

    private function assertSessionInfoResult(SessionInfo $result): void
    {
        static::assertSame($this->sessionInfo->getUserId()->getValue(), $result->getUserId()->getValue());
        static::assertSame($this->sessionInfo->getLatestIp()->getValue(), $result->getLatestIp()->getValue());
        static::assertSame($this->sessionInfo->getLatestDeviceId()->getValue(), $result->getLatestDeviceId()->getValue());
        static::assertSame($this->sessionInfo->getLatestUserAgent()->getValue(), $result->getLatestUserAgent()->getValue());
    }
}
