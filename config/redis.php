<?php

require_once __DIR__ . '/../vendor/autoload.php';

class RedisClient
{
    private static $instance = null;
    private $redis;

    private function __construct()
    {
        try {
            $host = $_ENV['REDIS_HOST'] ?? getenv('REDIS_HOST') ?? '127.0.0.1';
            $port = $_ENV['REDIS_PORT'] ?? getenv('REDIS_PORT') ?? '6379';
            $password = $_ENV['REDIS_PASSWORD'] ?? getenv('REDIS_PASSWORD') ?? null;

            $this->redis = new Predis\Client([
                'scheme' => 'tcp',
                'host'   => $host,
                'port'   => $port,
                'password' => !empty($password) ? $password : null,
            ]);

            // Test connection
            $this->redis->ping();
        } catch (Exception $e) {
            error_log("Redis Connection Error: " . $e->getMessage());
            throw new Exception("Redis connection failed");
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getClient()
    {
        return $this->redis;
    }

    private function __clone() {}
    public function __wakeup() {}
}
