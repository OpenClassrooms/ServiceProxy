<?php

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Tests\ParameterClassStub;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class CacheAnnotationWithConstructorClass
{
    const DATA = 'data';

    /**
     * @inheritDoc
     */
    public function __construct($argument)
    {

    }

    /**
     * @return bool
     */
    public function aMethodWithoutAnnotation()
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
    public function onlyCache()
    {
        return self::DATA;
    }

    /**
     * @Cache(lifetime=60)
     *
     * @return string
     */
    public function cacheWithLifeTime()
    {
        return self::DATA;
    }

    /**
     * @Cache(id="'test'")
     *
     * @return string
     */
    public function cacheWithId()
    {
        return self::DATA;
    }

    /**
     * @Cache(id="'test' ~ param1.publicField")
     *
     * @return string
     */
    public function cacheWithIdAndParameters(ParameterClassStub $param1, $param2)
    {
        return self::DATA;
    }

    /**
     * @Cache(namespace="'test-namespace'")
     */
    public function cacheWithNamespace()
    {
        return self::DATA;
    }

    /**
     * @Cache(namespace="'test-namespace' ~ param1.publicField")
     */
    public function cacheWithNamespaceAndParameters(ParameterClassStub $param1, $param2)
    {
        return self::DATA;
    }
}
