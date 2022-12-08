<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SessionInfo;
use App\Exception\NotFoundException;
use App\Exception\SessionInfoNotFoundException;
use App\Exception\SessionInfoServiceException;
use App\Repository\SessionInfoRepositoryInterface;
use App\VO\DeviceId;
use App\VO\IP;
use App\VO\Timestamp;
use App\VO\UserAgent;
use App\VO\UserId;
use Psr\Log\LoggerInterface;

class SessionInfoService
{
    public function __construct(
        private SessionInfoRepositoryInterface $sessionInfoRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function saveSessionInfo(
        UserId $userId,
        Timestamp $timestamp,
        ?DeviceId $deviceId,
        ?UserAgent $userAgent,
        ?IP $ip,
    ): void {
        try {
            $this->sessionInfoRepository->save(
                new SessionInfo(
                    $userId,
                    $timestamp,
                    $deviceId,
                    $userAgent,
                    $ip,
                )
            );
        } catch (\Throwable $e) {
            $this->logger->error('failed to save sessionInfo', [
                'user_id' => $userId->getValue(),
                'timestamp' => $timestamp->getValue(),
                'device_id' => (null !== $deviceId) ? $deviceId->getValue() : null,
                'user_agent' => (null !== $userAgent) ? $userAgent->getValue() : null,
                'ip' => (null !== $ip) ? $ip->getValue() : null,
                'message' => $e->getMessage(),
            ]);
            throw SessionInfoServiceException::failedToSave($e);
        }
    }

    /**
     * @throws NotFoundException
     */
    public function getByUserId(UserId $userId): SessionInfo
    {
        try {
            return $this->sessionInfoRepository->getByUserId($userId);
        } catch (SessionInfoNotFoundException $e) {
            throw NotFoundException::forSessionInfo();
        } catch (\Throwable $e) {
            $this->logger->error('failed to get sessionInfo', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw SessionInfoServiceException::failedToGet($e);
        }
    }
}
