<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Transaction\TransactionAdapterInterface;

class LoggingTransactionAdapter implements TransactionAdapterInterface
{
    public function beginTransaction(): bool
    {
        CallsLog::log();

        return true;
    }

    public function isTransactionActive(): bool
    {
        CallsLog::log();

        return false;
    }

    public function commit(): bool
    {
        CallsLog::log();

        return true;
    }

    public function rollBack(): bool
    {
        CallsLog::log();

        return true;
    }
}
