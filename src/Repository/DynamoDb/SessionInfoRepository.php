<?php

declare(strict_types=1);

namespace App\Repository\DynamoDb;

use App\Entity\SessionInfo;
use App\Exception\SessionInfoNotFoundException;
use App\Exception\SessionInfoRepositoryException;
use App\Repository\SessionInfoRepositoryInterface;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Psr\Log\LoggerInterface;

class SessionInfoRepository implements SessionInfoRepositoryInterface
{
    public function __construct(
        private string $sessionInfoTableName,
        private DynamoDbClient $client,
        private Marshaler $marshaller,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws SessionInfoRepositoryException
     * @throws SessionInfoNotFoundException
     */
    public function getByUserId(UserId $userId): SessionInfo
    {
        try {
            $item = $this->findItem([
                'TableName' => $this->sessionInfoTableName,
                'Key' => $this->marshaller->marshalJson(json_encode([
                    'user_id' => $userId->getValue(),
                ], JSON_THROW_ON_ERROR)),
            ]);
        } catch (DynamoDbException $ex) {
            $this->logger->error('error when querying sessionInfo for {user_id}', [
                'user_id' => $userId,
            ]);
            throw new SessionInfoRepositoryException('Find session info by user id exception: ' . $ex->getMessage(), $ex);
        }

        if (0 === count($item)) {
            $this->logger->error('sessionInfo not found for {user_id}', [
                'user_id' => $userId,
            ]);
            throw new SessionInfoNotFoundException();
        }

        return $this->createFromDBItem(array_shift($item));
    }

    /**
     * @throws SessionInfoRepositoryException
     */
    public function save(SessionInfo $info): void
    {
        $item = $this->marshaller->marshalJson(json_encode([
            'user_id' => $info->getUserId()->getValue(),
            'latest_device_id' => (null !== $info->getLatestDeviceId()) ? $info->getLatestDeviceId()->getValue() : null,
            'latest_user_agent' => (null !== $info->getLatestUserAgent()) ? $info->getLatestUserAgent()->getValue() : null,
            'latest_ip' => (null !== $info->getLatestIp()) ? $info->getLatestIp()->getValue() : null,
            'updated_at' => $info->getUpdatedAt()->getValue(),
        ], JSON_THROW_ON_ERROR));

        try {
            $this->client->putItem([
                'TableName' => $this->sessionInfoTableName,
                'Item' => $item,
            ]);
        } catch (DynamoDbException $ex) {
            throw new SessionInfoRepositoryException('Save session info exception: ' . $ex->getMessage(), $ex);
        }
    }

    private function createFromDBItem(array $itm): SessionInfo
    {
        return new SessionInfo(
            userId: UserId::fromInt((int) $itm['user_id']['N']),
            updatedAt: isset($itm['updated_at']['N']) ? Timestamp::fromInt((int) $itm['updated_at']['N']) : null,
            latestDeviceId: isset($itm['latest_device_id']['S']) ? DeviceId::fromString((string) $itm['latest_device_id']['S']) : null,
            latestUserAgent: isset($itm['latest_user_agent']['S']) ? UserAgent::fromString((string) $itm['latest_user_agent']['S']) : null,
            latestIp: isset($itm['latest_ip']['S']) ? IP::fromString((string) $itm['latest_ip']['S']) : null,
        );
    }

    private function findItem(array $params): array
    {
        $this->logger->debug('Execute DynamoDB scan/query with params', [
            'params' => $params,
        ]);

        $result = $this->client->getItem($params)->toArray();

        $this->logger->debug('DynamoDB scan/query result', [
            'result' => $result,
        ]);

        return $result['Item'] ?? [];
    }
}
