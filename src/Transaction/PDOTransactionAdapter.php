<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Transaction;

use OpenClassrooms\ServiceProxy\Exceptions\TransactionConflictException;

class PDOTransactionAdapter implements TransactionAdapterInterface
{
    public const CONFLICT_SQL_STATE_CODE = 23000;

    protected \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function isTransactionActive(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function commit(): bool
    {
        try {
            return $this->pdo->commit();
        } catch (\PDOException $exception) {
            if (self::CONFLICT_SQL_STATE_CODE === $exception->getCode()) {
                throw new TransactionConflictException('', 0, $exception);
            }

            throw $exception;
        }
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
