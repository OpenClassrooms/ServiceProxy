<?php

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub;

use OpenClassrooms\ServiceProxy\Annotations\Cache;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
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
