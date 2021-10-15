<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

class PDOMock extends \PDO
{
    public static ?\Exception $exception = null;

    public static bool $transactionBegan;

    public static bool $inTransaction;

    public static bool $committed;

    public static bool $rolledBack;

    public function __construct()
    {
        self::$transactionBegan = false;
        self::$inTransaction = false;
        self::$committed = false;
        self::$rolledBack = false;
        self::$exception = null;
    }

    public function beginTransaction(): bool
    {
        self::$transactionBegan = true;
        self::$inTransaction = true;

        return true;
    }

    public function inTransaction(): bool
    {
        return self::$inTransaction;
    }

    /**
     * @throws \Exception
     */
    public function commit(): bool
    {
        self::$inTransaction = false;

        if (null !== self::$exception) {
            throw self::$exception;
        }

        self::$committed = true;

        return true;
    }

    public function rollBack(): bool
    {
        self::$inTransaction = false;
        self::$rolledBack = true;

        return true;
    }
}
