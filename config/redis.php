<?php

require_once __DIR__ . '/../vendor/autoload.php';

class RedisClient
{
    private static $instance = null;
    private $redis;

    private function __construct()
    {
        try {
            $this->redis = new Predis\Client([
                'scheme' => 'tcp',
                'host'   => $_ENV['REDIS_HOST'],
                'port'   => $_ENV['REDIS_PORT'],
                'password' => !empty($_ENV['REDIS_PASSWORD']) ? $_ENV['REDIS_PASSWORD'] : null,
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
