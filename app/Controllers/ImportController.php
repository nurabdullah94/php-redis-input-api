<?php

namespace App\Controllers;

use App\Models\ImportJob;
use App\Queue\QueueManager;
use App\Services\Logger;
use PDO;

class ImportController
{
    private $db;
    private $importJobModel;
    private $queueManager;
    private $logger;

    public function __construct(PDO $db, QueueManager $queueManager)
    {
        $this->db = $db;
        $this->importJobModel = new ImportJob($db);
        $this->queueManager = $queueManager;
        $this->logger = new Logger();
    }

    /**
     * Upload and queue CSV import
     */
    public function upload(): void
    {
        try {
            // Validate file upload
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $this->sendErrorResponse('No file uploaded or upload error occurred', 400);
                return;
            }

            $file = $_FILES['file'];

            // Validate file size
            $maxSize = $_ENV['MAX_UPLOAD_SIZE'] ?? 10485760; // 10MB default
            if ($file['size'] > $maxSize) {
                $this->sendErrorResponse('File size exceeds maximum allowed size', 400);
                return;
            }

            // Validate file extension
            $allowedExtensions = explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'csv');
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                $this->sendErrorResponse('Invalid file type. Only CSV files are allowed', 400);
                return;
            }

            // Validate CSV structure
            $validation = $this->validateCsvFile($file['tmp_name']);
            if (!$validation['valid']) {
                $this->sendErrorResponse($validation['error'], 400);
                return;
            }

            // Generate unique filename
            $filename = uniqid('import_', true) . '.csv';
            $uploadPath = __DIR__ . '/../../uploads/' . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $this->sendErrorResponse('Failed to save uploaded file', 500);
                return;
            }

            // Get user data from request
            $userId = $_REQUEST['user_id'] ?? null;

            // Create import job
            $jobId = $this->importJobModel->create([
                'filename' => $filename,
                'original_filename' => $file['name'],
                'status' => 'pending',
                'total' => $validation['row_count'],
                'user_id' => $userId
            ]);

            // Push to queue
            $this->queueManager->push([
                'job_id' => $jobId,
                'filename' => $filename,
                'original_filename' => $file['name']
            ]);

            $this->logger->info("Import job created and queued", [
                'job_id' => $jobId,
                'filename' => $filename,
                'original_filename' => $file['name'],
                'rows' => $validation['row_count']
            ]);

            // Send response
            $this->sendSuccessResponse([
                'job_id' => $jobId,
                'status' => 'pending',
                'message' => 'File uploaded successfully and queued for processing'
            ], 201);

        } catch (\Exception $e) {
            $this->logger->error("Upload failed: " . $e->getMessage());
            $this->sendErrorResponse('Failed to process upload: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get import job status
     *
     * @param int $jobId
     */
    public function getStatus(int $jobId): void
    {
        try {
            $job = $this->importJobModel->findById($jobId);

            if (!$job) {
                $this->sendErrorResponse('Import job not found', 404);
                return;
            }

            // Get errors if job is completed or failed
            $errors = [];
            if (in_array($job['status'], ['completed', 'failed'])) {
                $errors = $this->importJobModel->getErrors($jobId);
            }

            $response = [
                'job_id' => (int) $job['id'],
                'status' => $job['status'],
                'filename' => $job['original_filename'],
                'total' => (int) $job['total'],
                'success' => (int) $job['success'],
                'failed' => (int) $job['failed'],
                'created_at' => $job['created_at'],
                'updated_at' => $job['updated_at']
            ];

            if ($job['error_message']) {
                $response['error_message'] = $job['error_message'];
            }

            if (!empty($errors)) {
                $response['errors'] = array_map(function($error) {
                    return [
                        'line_number' => (int) $error['line_number'],
                        'error_message' => $error['error_message'],
                        'row_data' => $error['row_data'] ? json_decode($error['row_data'], true) : null
                    ];
                }, array_slice($errors, 0, 10)); // Limit to first 10 errors

                $response['total_errors'] = count($errors);
            }

            $this->sendSuccessResponse($response);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get job status: " . $e->getMessage(), ['job_id' => $jobId]);
            $this->sendErrorResponse('Failed to get job status', 500);
        }
    }

    /**
     * Validate CSV file structure
     *
     * @param string $filepath
     * @return array
     */
    private function validateCsvFile(string $filepath): array
    {
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            return ['valid' => false, 'error' => 'Cannot read file'];
        }

        // Check header
        $header = fgetcsv($handle, 0, ',', '"', '');
        if (!$header || count($header) < 4) {
            fclose($handle);
            return ['valid' => false, 'error' => 'Invalid CSV format. Expected columns: name, sku, price, stock'];
        }

        // Expected headers (case-insensitive)
        $expectedHeaders = ['name', 'sku', 'price', 'stock'];
        $normalizedHeaders = array_map('strtolower', array_map('trim', $header));

        foreach ($expectedHeaders as $expected) {
            if (!in_array($expected, $normalizedHeaders)) {
                fclose($handle);
                return ['valid' => false, 'error' => "Missing required column: {$expected}"];
            }
        }

        // Count rows
        $rowCount = 0;
        while (fgetcsv($handle, 0, ',', '"', '') !== false) {
            $rowCount++;
        }

        fclose($handle);

        if ($rowCount === 0) {
            return ['valid' => false, 'error' => 'CSV file is empty'];
        }

        return ['valid' => true, 'row_count' => $rowCount];
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
