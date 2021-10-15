<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\Transaction;

class TransactionAnnotationClass
{
    public const DATA = ['id' => 29];

    public function aMethodWithoutAnnotation(): bool
    {
        return true;
    }

    /**
     * @Transaction
     *
     * @throws \Exception
     */
    public function transactionMethodWithExceptionWithoutConflictException(): void
    {
        throw new \Exception();
    }

    /**
     * @Transaction(onConflictThrow="\OpenClassrooms\ServiceProxy\Tests\Doubles\FunctionalConflictException")
     *
     * @throws \Exception
     */
    public function transactionMethodWithExceptionAndConflictException(): void
    {
        throw new \Exception();
    }

    /**
     * @Transaction
     */
    public function onlyTransaction(): array
    {
        return self::DATA;
    }

    /**
     * @Transaction(onConflictThrow="\OpenClassrooms\ServiceProxy\Tests\Doubles\FunctionalConflictException")
     */
    public function transactionWithConflictException(): array
    {
        return self::DATA;
    }
}
