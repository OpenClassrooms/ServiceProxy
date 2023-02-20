<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Contract;

interface TransactionHandler extends AnnotationHandler
{
    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollback(): bool;

    public function isTransactionActive(): bool;
}
