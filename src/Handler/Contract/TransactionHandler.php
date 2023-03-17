<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

interface TransactionHandler extends AnnotationHandler
{
    public function begin(): bool;

    public function commit(): bool;

    public function rollback(): bool;
}