<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

use OpenClassrooms\ServiceProxy\Handler\Exception\LockException;

interface LockHandler extends AnnotationHandler
{
    /**
     * @throws LockException
     */
    public function acquire(string $key): void;

    /**
     * @throws LockException
     */
    public function release(string $key): void;

    public function isAcquired(string $key): bool;
}
