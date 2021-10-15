<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Annotations\Transaction;

class CacheAndTransactionAnnotationClass
{
    public const DATA = 'data';

    public function aMethodWithoutAnnotation(): bool
    {
        return true;
    }

    /**
     * @Cache
     * @Transaction
     */
    public function cacheAndTransactionMethodWithVoidReturn(): void
    {
        $doSomething = static function () {};

        $doSomething();
    }
}
