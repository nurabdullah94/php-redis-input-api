<?php

namespace App\Models;

use PDO;
use Exception;

class ImportJob
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Create a new import job
     *
     * @param array $data
     * @return int Job ID
     * @throws Exception
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO import_jobs (filename, original_filename, status, total, user_id, created_at)
                VALUES (:filename, :original_filename, :status, :total, :user_id, NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':filename' => $data['filename'],
                ':original_filename' => $data['original_filename'],
                ':status' => $data['status'] ?? 'pending',
                ':total' => $data['total'] ?? 0,
                ':user_id' => $data['user_id'] ?? null
            ]);

            return (int) $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Failed to create import job: " . $e->getMessage());
        }
    }

    /**
     * Find import job by ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM import_jobs WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Update import job status
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        $sql = "UPDATE import_jobs SET status = :status, updated_at = NOW() WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':status' => $status,
                ':id' => $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update import job progress
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProgress(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params[':status'] = $data['status'];
        }

        if (isset($data['total'])) {
            $fields[] = "total = :total";
            $params[':total'] = $data['total'];
        }

        if (isset($data['success'])) {
            $fields[] = "success = :success";
            $params[':success'] = $data['success'];
        }

        if (isset($data['failed'])) {
            $fields[] = "failed = :failed";
            $params[':failed'] = $data['failed'];
        }

        if (isset($data['error_message'])) {
            $fields[] = "error_message = :error_message";
            $params[':error_message'] = $data['error_message'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE import_jobs SET " . implode(', ', $fields) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Log import error
     *
     * @param int $jobId
     * @param int $lineNumber
     * @param string $errorMessage
     * @param string|null $rowData
     * @return bool
     */
    public function logError(int $jobId, int $lineNumber, string $errorMessage, ?string $rowData = null): bool
    {
        $sql = "INSERT INTO import_errors (import_job_id, line_number, error_message, row_data, created_at)
                VALUES (:job_id, :line_number, :error_message, :row_data, NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':job_id' => $jobId,
                ':line_number' => $lineNumber,
                ':error_message' => $errorMessage,
                ':row_data' => $rowData
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get errors for a job
     *
     * @param int $jobId
     * @return array
     */
    public function getErrors(int $jobId): array
    {
        $sql = "SELECT * FROM import_errors WHERE import_job_id = :job_id ORDER BY line_number ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':job_id' => $jobId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
