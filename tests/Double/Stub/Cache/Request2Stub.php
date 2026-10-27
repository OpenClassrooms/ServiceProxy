<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use OpenClassrooms\ServiceProxy\Attribute\Cache\Tag;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\Cache\AutoTaggable;

class Request2Stub implements AutoTaggable
{
    #[Tag(prefix: ResponseStub::class)]
    public int $userId = 1111;
}
