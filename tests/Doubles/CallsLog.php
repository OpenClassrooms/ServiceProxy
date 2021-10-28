<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

class CallsLog
{
    private static array $logs = [];

    public static function log(): void
    {
        $backtrace = debug_backtrace()[1];

        self::$logs[] = [$backtrace['class'], $backtrace['function']];
    }

    public static function getLogs(): array
    {
        return self::$logs;
    }

    public static function reset(): void
    {
        self::$logs = [];
    }
}
