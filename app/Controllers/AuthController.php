<?php

namespace App\Controllers;

use App\Services\JWTService;
use App\Services\Logger;
use PDO;

class AuthController
{
    private $db;
    private $jwtService;
    private $logger;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->jwtService = new JWTService();
        $this->logger = new Logger();
    }

    /**
     * Login and generate JWT token
     */
    public function login(): void
    {
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                $this->sendErrorResponse('Invalid JSON input', 400);
                return;
            }

            // Validate input
            $username = $input['username'] ?? null;
            $password = $input['password'] ?? null;

            if (!$username || !$password) {
                $this->sendErrorResponse('Username and password are required', 400);
                return;
            }

            // Find user
            $user = $this->findUserByUsername($username);

            if (!$user) {
                $this->logger->warning("Login failed: user not found", ['username' => $username]);
                $this->sendErrorResponse('Invalid credentials', 401);
                return;
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->logger->warning("Login failed: invalid password", ['username' => $username]);
                $this->sendErrorResponse('Invalid credentials', 401);
                return;
            }

            // Generate JWT token
            $token = $this->jwtService->generateToken([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]);

            $this->logger->info("User logged in successfully", [
                'user_id' => $user['id'],
                'username' => $user['username']
            ]);

            // Send response
            $this->sendSuccessResponse([
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $_ENV['JWT_EXPIRY'] ?? 3600,
                'user' => [
                    'id' => (int) $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Login error: " . $e->getMessage());
            $this->sendErrorResponse('Login failed', 500);
        }
    }

    /**
     * Register new user
     */
    public function register(): void
    {
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                $this->sendErrorResponse('Invalid JSON input', 400);
                return;
            }

            // Validate input
            $username = $input['username'] ?? null;
            $email = $input['email'] ?? null;
            $password = $input['password'] ?? null;

            if (!$username || !$email || !$password) {
                $this->sendErrorResponse('Username, email and password are required', 400);
                return;
            }

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->sendErrorResponse('Invalid email format', 400);
                return;
            }

            // Check if user exists
            if ($this->findUserByUsername($username)) {
                $this->sendErrorResponse('Username already exists', 409);
                return;
            }

            if ($this->findUserByEmail($email)) {
                $this->sendErrorResponse('Email already exists', 409);
                return;
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Create user
            $sql = "INSERT INTO users (username, email, password, created_at)
                    VALUES (:username, :email, :password, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

            $userId = (int) $this->db->lastInsertId();

            $this->logger->info("User registered successfully", [
                'user_id' => $userId,
                'username' => $username
            ]);

            // Generate JWT token
            $token = $this->jwtService->generateToken([
                'user_id' => $userId,
                'username' => $username,
                'email' => $email
            ]);

            // Send response
            $this->sendSuccessResponse([
                'message' => 'User registered successfully',
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $_ENV['JWT_EXPIRY'] ?? 3600,
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email
                ]
            ], 201);

        } catch (\Exception $e) {
            $this->logger->error("Registration error: " . $e->getMessage());
            $this->sendErrorResponse('Registration failed', 500);
        }
    }

    /**
     * Get current user info (requires authentication)
     */
    public function me(): void
    {
        try {
            $token = $this->jwtService->extractTokenFromHeader();

            if (!$token) {
                $this->sendErrorResponse('Token not provided', 401);
                return;
            }

            $decoded = $this->jwtService->verifyToken($token);

            $user = $this->findUserById($decoded->user_id);

            if (!$user) {
                $this->sendErrorResponse('User not found', 404);
                return;
            }

            $this->sendSuccessResponse([
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'created_at' => $user['created_at']
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Get user info error: " . $e->getMessage());
            $this->sendErrorResponse('Failed to get user info', 500);
        }
    }

    /**
     * Find user by username
     *
     * @param string $username
     * @return array|null
     */
    private function findUserByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return array|null
     */
    private function findUserByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return array|null
     */
    private function findUserById(int $id): ?array
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Send success response
     *
     * @param array $data
     * @param int $statusCode
     */
    private function sendSuccessResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Send error response
     *
     * @param string $message
     * @param int $statusCode
     */
    private function sendErrorResponse(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
    }
}
