<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Doubles;

use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Tests\ParameterClassStub;

class CacheAnnotationClass
{
    public const DATA = 'data';

    public function aMethodWithoutAnnotation(): bool
    {
        return true;
    }

    /**
     * @Cache
     *
     * @throws \Exception
     */
    public function cacheMethodWithException(): void
    {
        throw new \Exception();
    }

    /**
     * @Cache
     */
    public function cacheMethodWithVoidReturn(): void
    {
        $doSomething = static function () {};

        $doSomething();
    }

    public function methodWithVoidReturn(): void
    {
        $doSomething = static function () {};

        $doSomething();
    }

    /**
     * @Cache
     */
    public function onlyCache(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(lifetime=60)
     */
    public function cacheWithLifeTime(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(id="'test'")
     */
    public function cacheWithId(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(id="'test' ~ param1.publicField")
     */
    public function cacheWithIdAndParameters(ParameterClassStub $param1, $param2): string
    {
        return self::DATA;
    }

    /**
     * @Cache(namespace="'test-namespace'")
     */
    public function cacheWithNamespace(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(namespace="'test-namespace' ~ param1.publicField")
     */
    public function cacheWithNamespaceAndParameters(ParameterClassStub $param1, $param2): string
    {
        return self::DATA;
    }
}
