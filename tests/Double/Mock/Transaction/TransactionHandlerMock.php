<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Transaction;

use OpenClassrooms\ServiceProxy\Contract\TransactionHandler;

final class TransactionHandlerMock implements TransactionHandler
{
    public bool $committed = false;

    public bool $rollBacked = false;

    public function getName(): string
    {
        return 'array';
    }

    public function beginTransaction(): bool
    {
        return true;
    }

    public function commit(): bool
    {
        $this->committed = true;

        return true;
    }

    public function rollback(): bool
    {
        $this->rollBacked = true;

        return true;
    }

    public function isTransactionActive(): bool
    {
        return true;
    }
}
