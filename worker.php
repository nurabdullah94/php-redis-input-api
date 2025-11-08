#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Queue\ImportWorker;
use App\Queue\QueueManager;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
} catch (Exception $e) {
    // Try safeLoad if load fails (for older versions)
    $dotenv->safeLoad();
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "===========================================\n";
echo "Product Import Queue Worker\n";
echo "===========================================\n\n";

try {
    // Initialize database
    require_once __DIR__ . '/config/database.php';
    $db = Database::getInstance()->getConnection();

    echo "Database connected successfully\n";

    // Initialize Redis
    require_once __DIR__ . '/config/redis.php';
    $redis = RedisClient::getInstance()->getClient();
    $queueManager = new QueueManager($redis);

    echo "Redis connected successfully\n";
    echo "Queue: {$queueManager->getQueueName()}\n\n";

    // Start worker
    $worker = new ImportWorker($db, $queueManager);
    $worker->start();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("Worker Error: " . $e->getMessage());
    exit(1);
}
