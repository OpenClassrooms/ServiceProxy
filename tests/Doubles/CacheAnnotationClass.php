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
}
