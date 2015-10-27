<?php

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class WithoutAnnotationClass
{
    /**
     * @return bool
     */
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
