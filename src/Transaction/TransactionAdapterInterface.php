<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Transaction;

interface TransactionAdapterInterface
{
    public function beginTransaction(): bool;

    public function isTransactionActive(): bool;

    /**
     * @throws \OpenClassrooms\ServiceProxy\Exceptions\TransactionConflictException
     */
    public function commit(): bool;

    public function rollBack(): bool;
}
