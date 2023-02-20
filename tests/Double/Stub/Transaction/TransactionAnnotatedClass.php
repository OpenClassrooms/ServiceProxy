<?php /** @noinspection PhpUnusedParameterInspection */

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Transaction;

use OpenClassrooms\ServiceProxy\Annotations\Transaction;

class TransactionAnnotatedClass
{
    public function nonAnnotatedMethod(): bool
    {
        return true;
    }

    /**
     * @Transaction
     */
    public function annotatedMethodWithException(): void
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
    public function nestedAnnotatedMethodWithException(): void
    {
        $self = new self();
        $self->annotatedMethodWithException();
    }

    /**
     * @Transaction
     */
    public function doubleAnnotatedMethod($useCaseRequest): int
    {
        return 1;
    }
}
