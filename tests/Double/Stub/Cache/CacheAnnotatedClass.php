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
     * @Cache(namespace="'test_namespace'", id="'test_id'")
     */
    public function cacheWithNamespaceAndId(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(namespace="'test_namespace' ~ param1.getPrivateField()", id="'test_id' ~ param2")
     * @noinspection PhpUnusedParameterInspection
     */
    public function cacheWithNamespaceIdAndParameters(ParameterClassStub $param1, $param2): string
    {
        return self::DATA;
    }

    /**
     * @Cache(id="'test_id'", tags={"'custom_tag'"})
     */
    public function cacheWithIdAndTags(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(tags={"'custom_tag' ~ param1.publicField"})
     */
    public function cacheWithTagsAndParameters(ParameterClassStub $param1, $param2): string
    {
        return self::DATA;
    }

    /**
     * @Cache(version=2)
     */
    public function cacheWithVersion(): string
    {
        return self::DATA;
    }

    /**
     * @Cache(id="'test_id' ~ param1.getPrivateField()", version=2)
     */
    public function cacheWithIdAndVersion(ParameterClassStub $param1): string
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
