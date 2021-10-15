<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

class UnsupportedServiceProxyAnnotationClass
{
    /**
     * @UnsupportedServiceProxyAnnotation
     */
    public function methodWithAnnotation(): bool
    {
        return true;
    }
}
