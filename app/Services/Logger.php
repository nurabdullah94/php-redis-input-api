<?php

namespace App\Services;

class Logger
{
    private $logPath;

    public function __construct()
    {
        $this->logPath = __DIR__ . '/../../logs/';
    }

    /**
     * Log info message
     *
     * @param string $message
     * @param array $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Log error message
     *
     * @param string $message
     * @param array $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Log warning message
     *
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Log debug message
     *
     * @param string $message
     * @param array $context
     */
    public function debug(string $message, array $context = []): void
    {
        if ($_ENV['APP_DEBUG'] ?? false) {
            $this->log('DEBUG', $message, $context);
        }
    }

    /**
     * Write log to file
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        $date = date('Y-m-d');
        $filename = $this->logPath . "app-{$date}.log";

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        // Create directory if not exists
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }

        file_put_contents($filename, $logMessage, FILE_APPEND);

        // Also log to error_log for errors
        if ($level === 'ERROR') {
            error_log($logMessage);
        }
    }
}
