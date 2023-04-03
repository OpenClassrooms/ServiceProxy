<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use OpenClassrooms\ServiceProxy\Annotation\Cache;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ParameterClassStub;

class LegacyCacheAnnotatedClass
{
    public const DATA = 'data';

    public function nonAnnotatedMethod(): bool
    {
        return true;
    }

    /**
     * @Cache(handler="legacy_array")
     */
    public function annotatedMethodWithException(): array
    {
        throw new \RuntimeException();
    }

    /**
     * @Cache(handler="legacy_array")
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
     * @Cache(handler="legacy_array")
     */
    public function annotatedMethod(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(handler="legacy_array", lifetime=60)
     */
    public function cacheWithLifeTime(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(handler="legacy_array", id="'test'")
     */
    public function cacheWithId(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(handler="legacy_array", id="'test' ~ param1.publicField")
     * @noinspection PhpUnusedParameterInspection
     */
    public function cacheWithIdAndParameters(ParameterClassStub $param1, $param2): string
    {
        return self::DATA;
    }

    /**
     * @Cache(handler="legacy_array", namespace="'test-namespace'")
     */
    public function cacheWithNamespace(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(handler="legacy_array", namespace="'test-namespace'", id="'toto'")
     */
    public function cacheWithNamespaceAndId(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(handler="legacy_array", namespace="'test-namespace' ~ param1.publicField")
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