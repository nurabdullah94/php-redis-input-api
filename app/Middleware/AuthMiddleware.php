<?php

namespace App\Middleware;

use App\Services\JWTService;
use App\Services\Logger;
use Exception;

class AuthMiddleware
{
    private $jwtService;
    private $logger;

    public function __construct()
    {
        $this->jwtService = new JWTService();
        $this->logger = new Logger();
    }

    /**
     * Handle authentication
     *
     * @return array|null User data if authenticated, null otherwise
     */
    public function handle(): ?array
    {
        try {
            // Extract token from header
            $token = $this->jwtService->extractTokenFromHeader();

            if (!$token) {
                $this->sendUnauthorizedResponse('Token not provided');
                return null;
            }

            // Verify token
            $decoded = $this->jwtService->verifyToken($token);

            // Return user data
            return [
                'user_id' => $decoded->user_id ?? null,
                'username' => $decoded->username ?? null,
                'email' => $decoded->email ?? null
            ];

        } catch (Exception $e) {
            $this->logger->error('Authentication failed: ' . $e->getMessage());
            $this->sendUnauthorizedResponse($e->getMessage());
            return null;
        }
    }

    /**
     * Send unauthorized response
     *
     * @param string $message
     */
    private function sendUnauthorizedResponse(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized',
            'error' => $message
        ]);
        exit;
    }
}
