<?php

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Tests\ParameterClassStub;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class CacheAnnotationClass
{
    const DATA = 'data';

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
    public function aMethodWithParameters(ParameterClassStub $param1, $param2)
    {
        return true;
    }

    /**
     * @Cache
     */
    public function cacheMethodWithException()
    {
        throw new \Exception();
    }

    /**
     * @Cache
     */
    public function onlyCacheMethod()
    {
        return self::DATA;
    }

    /**
     * @Cache(lifetime=60)
     * @return string
     */
    public function cacheWithLifeTime()
    {
        return self::DATA;
    }

    /**
     * @Cache(namespace="'test-namespace'")
     */
    public function cacheWithNamespaceMethod()
    {
        return self::DATA;
    }

    /**
     * @Cache(namespace="'test-namespace' ~ param1.publicField")
     */
    public function cacheWithNamespaceAndParametersMethod(ParameterClassStub $param1, $param2)
    {
        return self::DATA;
    }

}
