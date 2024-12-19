<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock;

use Psr\Log\LoggerInterface;

class LoggerMock implements LoggerInterface
{
    private array $logs = [];

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => 'alert',
            'message' => $message,
            'context' => $context,
        ];
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => 'critical',
            'message' => $message,
            'context' => $context,
        ];
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => 'debug',
            'message' => $message,
            'context' => $context,
        ];
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => 'emergency',
            'message' => $message,
            'context' => $context,
        ];
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => 'error',
            'message' => $message,
            'context' => $context,
        ];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => 'info',
            'message' => $message,
            'context' => $context,
        ];
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => 'notice',
            'message' => $message,
            'context' => $context,
        ];
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => 'warning',
            'message' => $message,
            'context' => $context,
        ];
    }
}
