<?php

declare(strict_types=1);

/** @noinspection PhpUnusedParameterInspection */

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Transaction;

use OpenClassrooms\ServiceProxy\Annotation\Transaction;

class TransactionAnnotatedClass
{
    public function nonAnnotatedMethod(): bool
    {
        return true;
    }

    /**
     * @Transaction
     */
    public function annotatedMethodThatThrowsException(): void
    {
        throw new \RuntimeException();
    }

    /**
     * @Transaction
     */
    public function annotatedMethod(): void
    {
    }

    /**
     * @Transaction(exceptions={
     *     "\RuntimeException"="\InvalidArgumentException"
     * })
     */
    public function annotatedMethodWithExceptionMappingThatThrowsException(): void
    {
        throw new \RuntimeException();
    }

    /**
     * @Transaction
     */
    public function nestedAnnotatedMethod(): void
    {
        $self = new self();
        $self->annotatedMethod();
    }

    /**
     * @Transaction
     */
    public function nestedAnnotatedMethodThatThrowsException(): void
    {
        $self = new self();
        $self->annotatedMethodThatThrowsException();
    }

    /**
     * @Transaction
     */
    public function doubleAnnotatedMethod($useCaseRequest): int
    {
        return 1;
    }
}
