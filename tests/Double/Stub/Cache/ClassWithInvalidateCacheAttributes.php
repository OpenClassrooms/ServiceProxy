<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use OpenClassrooms\ServiceProxy\Attribute\Cache;
use OpenClassrooms\ServiceProxy\Attribute\InvalidateCache;

class ClassWithInvalidateCacheAttributes
{
    public const DATA = 'data';

    #[Cache(tags: ['"my_tag"'])]
    public function methodWithTaggedCache(): string
    {
        return self::DATA;
    }

    #[InvalidateCache(tags: ['"my_tag"'])]
    public function methodWithInvalidateCacheAttribute(): void
    {
        return;
    }

    #[InvalidateCache(tags: ['"my_tag"'])]
    public function methodWithInvalidateCacheAndException(): void
    {
        throw new \Exception();
    }
}