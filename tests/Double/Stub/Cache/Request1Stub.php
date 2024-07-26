<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use OpenClassrooms\ServiceProxy\Attribute\Cache\Tag;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\Cache\AutoTaggable;

class Request1Stub implements AutoTaggable
{
    public const ID = 12;

    #[Tag]
    public int $age = 10;

    #[Tag(prefix: 'prefix')]
    public string $city = 'paris';

    public string $foo = 'bar';

    public int $id = 1;

    public function getId(): int
    {
        return self::ID;
    }

    #[Tag]
    public function getName(): string
    {
        return 'test';
    }

    public function getSomething(): string
    {
        return 'something';
    }

    public function getUserId(): int
    {
        return 1111;
    }
}
