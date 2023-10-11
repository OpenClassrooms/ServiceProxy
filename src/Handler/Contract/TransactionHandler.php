<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

interface TransactionHandler extends AnnotationHandler
{
    /**
     * @param string[] $entityManagers
     */
    public function begin(array $entityManagers): bool;

    /**
     * @param string[] $entityManagers
     */
    public function commit(array $entityManagers): bool;

    /**
     * @param string[] $entityManagers
     */
    public function rollback(array $entityManagers): bool;
}
