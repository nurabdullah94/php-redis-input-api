<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTService
{
    private $secret;
    private $expiry;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? 'default-secret-key';
        $this->expiry = $_ENV['JWT_EXPIRY'] ?? 3600;
    }

    /**
     * Generate JWT token
     *
     * @param array $payload
     * @return string
     */
    public function generateToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->expiry;

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expire
        ]);

        return JWT::encode($tokenPayload, $this->secret, 'HS256');
    }

    /**
     * Verify and decode JWT token
     *
     * @param string $token
     * @return object
     * @throws Exception
     */
    public function verifyToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (Exception $e) {
            throw new Exception('Invalid or expired token: ' . $e->getMessage());
        }
    }

    /**
     * Extract token from Authorization header
     *
     * @return string|null
     */
    public function extractTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            return null;
        }

        // Bearer token format
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
