<?php

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class WithoutAnnotationClass
{
    public function aMethodWithoutAnnotation()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function aMethodWithoutServiceProxyAnnotation()
    {
        return true;
    }
}
