<?php

namespace App\Queue;

use App\Models\Product;
use App\Models\ImportJob;
use App\Services\Logger;
use PDO;

class ImportWorker
{
    private $db;
    private $queueManager;
    private $logger;
    private $productModel;
    private $importJobModel;

    public function __construct(PDO $db, QueueManager $queueManager)
    {
        $this->db = $db;
        $this->queueManager = $queueManager;
        $this->logger = new Logger();
        $this->productModel = new Product($db);
        $this->importJobModel = new ImportJob($db);
    }

    /**
     * Start processing jobs from queue
     */
    public function start(): void
    {
        $this->logger->info("Worker started", [
            'queue' => $this->queueManager->getQueueName(),
            'pid' => getmypid()
        ]);

        echo "Worker started. Waiting for jobs...\n";
        echo "Queue: {$this->queueManager->getQueueName()}\n";
        echo "Press Ctrl+C to stop\n\n";

        // Handle graceful shutdown (only if PCNTL extension is available)
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
            pcntl_signal(SIGINT, [$this, 'shutdown']);
        } else {
            echo "Note: PCNTL extension not available (Windows). Use Ctrl+C to stop.\n\n";
        }

        while (true) {
            // Dispatch signals if available
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            // Pop job from queue with 5 second timeout
            $job = $this->queueManager->pop(5);

            if ($job) {
                $this->processJob($job);
            }

            // Small sleep to prevent CPU spinning
            usleep(100000); // 0.1 seconds
        }
    }

    /**
     * Process a single job
     *
     * @param array $job
     */
    private function processJob(array $job): void
    {
        $jobId = $job['job_id'] ?? null;
        $filename = $job['filename'] ?? null;

        if (!$jobId || !$filename) {
            $this->logger->error("Invalid job data", ['job' => $job]);
            return;
        }

        $this->logger->info("Processing job", ['job_id' => $jobId, 'filename' => $filename]);
        echo "[" . date('Y-m-d H:i:s') . "] Processing job #{$jobId}...\n";

        try {
            // Update status to in_progress
            $this->importJobModel->updateStatus($jobId, 'in_progress');

            // Process the CSV file
            $result = $this->processCsvFile($jobId, $filename);

            // Update final status
            $status = ($result['failed'] > 0 && $result['success'] === 0) ? 'failed' : 'completed';

            $this->importJobModel->updateProgress($jobId, [
                'status' => $status,
                'success' => $result['success'],
                'failed' => $result['failed']
            ]);

            $this->logger->info("Job completed", [
                'job_id' => $jobId,
                'success' => $result['success'],
                'failed' => $result['failed']
            ]);

            echo "[" . date('Y-m-d H:i:s') . "] Job #{$jobId} completed: {$result['success']} success, {$result['failed']} failed\n\n";

        } catch (\Exception $e) {
            $this->logger->error("Job failed", [
                'job_id' => $jobId,
                'error' => $e->getMessage()
            ]);

            $this->importJobModel->updateProgress($jobId, [
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            echo "[" . date('Y-m-d H:i:s') . "] Job #{$jobId} failed: {$e->getMessage()}\n\n";
        }
    }

    /**
     * Process CSV file
     *
     * @param int $jobId
     * @param string $filename
     * @return array
     */
    private function processCsvFile(int $jobId, string $filename): array
    {
        $filepath = __DIR__ . '/../../uploads/' . $filename;

        if (!file_exists($filepath)) {
            throw new \Exception("File not found: {$filename}");
        }

        $handle = fopen($filepath, 'r');
        if (!$handle) {
            throw new \Exception("Cannot open file: {$filename}");
        }

        $success = 0;
        $failed = 0;
        $rowNumber = 0;

        // Skip header row
        fgetcsv($handle, 0, ',', '"', '');

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $rowNumber++;

            try {
                // Validate row has 4 columns
                if (count($row) < 4) {
                    throw new \Exception("Invalid number of columns");
                }

                // Parse data
                $data = [
                    'name' => trim($row[0]),
                    'sku' => trim($row[1]),
                    'price' => floatval($row[2]),
                    'stock' => intval($row[3])
                ];

                // Validate data
                $validation = $this->validateProductData($data);
                if (!$validation['valid']) {
                    throw new \Exception($validation['error']);
                }

                // Insert or update product
                $this->productModel->upsert($data);
                $success++;

            } catch (\Exception $e) {
                $failed++;
                $errorMessage = "Row {$rowNumber}: " . $e->getMessage();

                $this->importJobModel->logError(
                    $jobId,
                    $rowNumber,
                    $errorMessage,
                    json_encode($row)
                );

                $this->logger->warning($errorMessage, [
                    'job_id' => $jobId,
                    'row' => $row
                ]);
            }

            // Update progress every 100 rows
            if ($rowNumber % 100 === 0) {
                $this->importJobModel->updateProgress($jobId, [
                    'success' => $success,
                    'failed' => $failed
                ]);

                echo "  Progress: {$rowNumber} rows processed ({$success} success, {$failed} failed)\n";
            }
        }

        fclose($handle);

        return [
            'success' => $success,
            'failed' => $failed,
            'total' => $rowNumber
        ];
    }

    /**
     * Validate product data
     *
     * @param array $data
     * @return array
     */
    private function validateProductData(array $data): array
    {
        if (empty($data['name'])) {
            return ['valid' => false, 'error' => 'Product name is required'];
        }

        if (empty($data['sku'])) {
            return ['valid' => false, 'error' => 'SKU is required'];
        }

        if (!is_numeric($data['price']) || $data['price'] < 0) {
            return ['valid' => false, 'error' => 'Price must be a positive number'];
        }

        if (!is_numeric($data['stock']) || $data['stock'] < 0) {
            return ['valid' => false, 'error' => 'Stock must be a positive number'];
        }

        return ['valid' => true];
    }

    /**
     * Shutdown handler
     */
    public function shutdown(): void
    {
        $this->logger->info("Worker shutting down");
        echo "\nWorker shutting down...\n";
        exit(0);
    }
}
