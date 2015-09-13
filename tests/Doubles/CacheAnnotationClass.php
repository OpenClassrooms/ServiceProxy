<?php

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\Cache;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class CacheAnnotationClass
{
    /**
     * @Cache
     */
    public function aMethod()
    {
        return true;
    }

    /**
     * @Cache
     */
    public function aMethodWithParameters(Cache $param1, $param2)
    {
        return true;
    }

}
