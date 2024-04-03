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

    #[InvalidateCache]
    public function methodWithInvalidateCacheButNoTagNorResponseObject(): string
    {
        return self::DATA;
    }

    #[Cache]
    public function methodWithCacheButNoTag(): ResponseStub
    {
        return new ResponseStub();
    }

    #[InvalidateCache]
    public function methodWithInvalidateCacheButNoTag(): ResponseStub
    {
        return new ResponseStub();
    }

    #[InvalidateCache(tags: ['"OpenClassrooms.ServiceProxy.Tests.Double.Stub.Cache.ResponseStub.12"'])]
    public function methodWithInvalidateCacheAndExplicitTag(): ResponseStub
    {
        return new ResponseStub();
    }

    #[Cache]
    public function methodWithCachedEmbeddedResponse(): ResponseStub
    {
        return new ResponseStub(new EmbeddedResponseStub());
    }

    #[InvalidateCache]
    public function methodInvalidatingSubResource(): EmbeddedResponseStub
    {
        return new EmbeddedResponseStub();
    }
}
