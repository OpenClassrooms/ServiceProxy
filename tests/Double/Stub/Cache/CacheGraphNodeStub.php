<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use OpenClassrooms\ServiceProxy\Interceptor\Contract\Cache\AutoTaggable;

class CacheGraphNodeStub implements AutoTaggable
{
    /**
     * @var array<AutoTaggable>
     */
    public array $children = [];
}
