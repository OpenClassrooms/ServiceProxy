<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\Transaction;

class TransactionAnnotationWithConstructorClass
{
    public const DATA = ['id' => 29];

    public function __construct($argument)
    {
    }

    public function aMethodWithoutAnnotation(): bool
    {
        return true;
    }

    /**
     * @Transaction
     */
    public function onlyTransaction(): array
    {
        return self::DATA;
    }
}
