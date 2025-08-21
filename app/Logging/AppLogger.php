<?php

namespace App\Logging;

use Illuminate\Support\Facades\Log;

/**
 * AppLogger is a custom logger for the application that formats log messages
 * with a specific context and additional fields.
 *
 * It provides methods for logging messages at different levels (info, warning,
 * error, debug) and allows setting a context for the logs.
 */
class AppLogger
{
    protected string $context;
    protected string $channel;
    protected array $fields = [];

    public function __construct(string $context = 'general', string $channel = 'stack')
    {
        $this->context = $context;
        $this->channel = $channel;
    }

    /**
     * Set the context for the logger.
     * @param string $context
     * @return void
     */
    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    /**
     * Set additional fields to be included in the log messages.
     * @param string $message
     * @param array $data
     * @return void
     */
    public function info(string $message, array $data = []): void
    {
        Log::channel($this->channel)->info($this->format($message, $data));
    }

    /**
     * Log a warning message.
     * @param string $message
     * @param array $data
     * @return void
     */
    public function warning(string $message, array $data = []): void
    {
        Log::channel($this->channel)->warning($this->format($message, $data));
    }

    /**
     * Log an error message.
     * @param string $message
     * @param array $data
     * @return void
     */
    public function error(string $message, array $data = []): void
    {
        Log::channel($this->channel)->error($this->format($message, $data));
    }

    /**
     * Log a debug message.
     * @param string $message
     * @param array $data
     * @return void
     */
    public function debug(string $message, array $data = []): void
    {
        Log::channel($this->channel)->debug($this->format($message, $data));
    }

    /**
     * Format a log message with context and additional fields.
     * Example output:
     * [2025-07-05 15:42:10][LOGIN] Login failed | request=%7B%22email%22%3A%22test%40example.com%22%7D&errors=Invalid+credentials
     */
    protected function format(string $message, array $data = []): string
    {
        $context = strtoupper($this->context);
        $merged = array_merge($this->fields, $data);
        $flattened = collect($merged)->map(function ($value) {
            if (is_scalar($value)) return $value;
            if ($value instanceof \JsonSerializable) return json_encode($value);
            if (is_array($value)) return json_encode($value);
            if (is_object($value)) return method_exists($value, '__toString') ? (string) $value : json_encode($value);
            return var_export($value, true);
        })->toArray();

        $pairs = collect($flattened)->map(fn($v, $k) => $k . '="' . str_replace('"', '\"', (string) $v) . '"')->implode(' | ');
        return "[$context] $message" . ($pairs ? " | $pairs" : '');
    }
}
