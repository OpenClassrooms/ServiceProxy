<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use OpenClassrooms\ServiceProxy\Annotation\Cache;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ParameterClassStub;

class CacheAnnotatedClass
{
    public const DATA = 'data';

    public function nonAnnotatedMethod(): bool
    {
        return true;
    }

    /**
     * @Cache
     */
    public function annotatedMethodWithException(): array
    {
        throw new \RuntimeException();
    }

    /**
     * @Cache
     */
    public function annotatedMethodWithVoidReturn(): void
    {
        $doSomething = static function () {
        };

        $doSomething();
    }

    public function nonAnnotatedMethodWithVoidReturn(): void
    {
        $doSomething = static function () {
        };

        $doSomething();
    }

    /**
     * @Cache
     */
    public function annotatedMethod(): string
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
     * @noinspection PhpUnusedParameterInspection
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
     * @noinspection PhpUnusedParameterInspection
     */
    public function cacheWithNamespaceAndParameters(ParameterClassStub $param1, $param2): string
    {
        return self::DATA;
    }

    /**
     * @Cache(handler="toto")
     */
    public function invalidHandler(): string
    {
        return self::DATA;
    }
}
