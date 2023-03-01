<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub;

use OpenClassrooms\ServiceProxy\Annotation\Cache;

class WithConstructorAnnotationClass
{
    public const DATA = 'data';

    public function __construct($argument)
    {
    }

    public function aMethodWithoutAnnotation(): bool
    {
        return true;
    }

    /**
     * @Cache
     */
    public function onlyCache(): string
    {
        return self::DATA;
    }
}
