<?php

namespace App\Queue;

use Predis\Client as Redis;
use App\Services\Logger;

class QueueManager
{
    private $redis;
    private $queueName;
    private $logger;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
        $this->queueName = $_ENV['REDIS_QUEUE_NAME'] ?? 'product_import_queue';
        $this->logger = new Logger();
    }

    /**
     * Push job to queue
     *
     * @param array $jobData
     * @return bool
     */
    public function push(array $jobData): bool
    {
        try {
            $payload = json_encode($jobData);
            $this->redis->rpush($this->queueName, [$payload]);

            $this->logger->info("Job pushed to queue", [
                'job_id' => $jobData['job_id'] ?? null,
                'queue' => $this->queueName
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to push job to queue: " . $e->getMessage(), [
                'job_data' => $jobData
            ]);
            return false;
        }
    }

    /**
     * Pop job from queue (blocking)
     *
     * @param int $timeout Timeout in seconds
     * @return array|null
     */
    public function pop(int $timeout = 0): ?array
    {
        try {
            $result = $this->redis->blpop([$this->queueName], $timeout);

            if ($result && isset($result[1])) {
                $jobData = json_decode($result[1], true);

                $this->logger->info("Job popped from queue", [
                    'job_id' => $jobData['job_id'] ?? null,
                    'queue' => $this->queueName
                ]);

                return $jobData;
            }

            return null;
        } catch (\Exception $e) {
            $this->logger->error("Failed to pop job from queue: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get queue size
     *
     * @return int
     */
    public function size(): int
    {
        try {
            return (int) $this->redis->llen($this->queueName);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get queue size: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clear queue
     *
     * @return bool
     */
    public function clear(): bool
    {
        try {
            $this->redis->del([$this->queueName]);
            $this->logger->info("Queue cleared", ['queue' => $this->queueName]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to clear queue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get queue name
     *
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }
}
