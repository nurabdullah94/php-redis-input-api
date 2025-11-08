<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\ImportController;
use App\Middleware\AuthMiddleware;
use App\Queue\QueueManager;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
try {
    $dotenv->load();
} catch (Exception $e) {
    // Try safeLoad if load fails (for older versions)
    $dotenv->safeLoad();
}

// Error handling
$appDebug = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? true;
error_reporting($appDebug ? E_ALL : 0);
ini_set('display_errors', $appDebug ? '1' : '0');

// Set headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request URI and method
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string and base path
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = str_replace('/php-redis-import-api/public', '', $uri);

try {
    // Initialize database
    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->getConnection();

    // Initialize Redis
    require_once __DIR__ . '/../config/redis.php';
    $redis = RedisClient::getInstance()->getClient();
    $queueManager = new QueueManager($redis);

    // Route handling
    switch (true) {
        // Health check
        case $uri === '/' && $requestMethod === 'GET':
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Product Import API is running',
                'version' => '1.0.0',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        // Authentication routes (no auth required)
        case $uri === '/api/auth/login' && $requestMethod === 'POST':
            $controller = new AuthController($db);
            $controller->login();
            break;

        case $uri === '/api/auth/register' && $requestMethod === 'POST':
            $controller = new AuthController($db);
            $controller->register();
            break;

        case $uri === '/api/auth/me' && $requestMethod === 'GET':
            // Requires authentication
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->handle();

            if ($user) {
                $controller = new AuthController($db);
                $controller->me();
            }
            break;

        // Import routes (requires authentication)
        case $uri === '/api/import/products' && $requestMethod === 'POST':
            // Authenticate request
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->handle();

            if ($user) {
                // Add user_id to request for tracking
                $_REQUEST['user_id'] = $user['user_id'];

                $controller = new ImportController($db, $queueManager);
                $controller->upload();
            }
            break;

        case preg_match('/^\/api\/import\/status\/(\d+)$/', $uri, $matches) && $requestMethod === 'GET':
            // Authenticate request
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->handle();

            if ($user) {
                $jobId = (int) $matches[1];
                $controller = new ImportController($db, $queueManager);
                $controller->getStatus($jobId);
            }
            break;

        // Queue status (requires authentication)
        case $uri === '/api/queue/status' && $requestMethod === 'GET':
            // Authenticate request
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->handle();

            if ($user) {
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'queue_name' => $queueManager->getQueueName(),
                        'pending_jobs' => $queueManager->size()
                    ]
                ]);
            }
            break;

        // 404 Not Found
        default:
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint not found',
                'requested_uri' => $uri,
                'method' => $requestMethod
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : 'An error occurred'
    ]);
}
