<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use OpenClassrooms\ServiceProxy\Attribute\Cache;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ParameterClassStub;

class ClassWithCacheAttributes
{
    public const DATA = 'data';

    public function methodWithoutAttribute(): bool
    {
        return true;
    }

    #[Cache]
    public function methodWithAttribute(): string
    {
        return self::DATA;
    }

    #[Cache]
    public function methodWithAttributeReturningObject(): Stub
    {
        return new Stub();
    }

    #[Cache]
    public function methodWithVoidReturn(): void
    {
        $doSomething = static function () {
        };

        $doSomething();
    }

    #[Cache]
    public function methodWithArguments(string $foo, string $bar): string
    {
        return self::DATA;
    }

    #[Cache]
    public function methodWithException(): string
    {
        throw new \Exception();

        return self::DATA;
    }

    #[Cache(ttl: 60)]
    public function methodWithLifetime(): string
    {
        return self::DATA;
    }

    #[Cache(handler: 'toto')]
    public function invalidHandler(): string
    {
        return self::DATA;
    }

    #[Cache(pools: ['toto'])]
    public function invalidPool(): string
    {
        return self::DATA;
    }

    #[Cache(handler: 'array', pools: ['default'])]
    public function bothHandlerAndPool(): string
    {
        return self::DATA;
    }

    #[Cache(pools: ['default'])]
    public function methodWithPool(): string
    {
        return self::DATA;
    }

    #[Cache(tags: ['"my_tag"'])]
    public function methodWithTaggedCache(): string
    {
        return self::DATA;
    }

    #[Cache(tags: ['"my_tag" ~ param.publicField'])]
    public function methodWithResolvedTag(ParameterClassStub $param): string
    {
        return self::DATA;
    }

    #[Cache(pools: ['foo', 'bar'])]
    public function methodWithMultiplePools(): string
    {
        return self::DATA;
    }
}

class Stub
{
    public const ID = 12;

    private $id = self::ID;
}
