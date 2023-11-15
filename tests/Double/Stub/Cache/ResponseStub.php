<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use OpenClassrooms\ServiceProxy\Interceptor\Contract\Cache\AutoTaggable;

class ResponseStub implements AutoTaggable
{
    public const ID = 12;

    public function __construct(
        private ?EmbeddedResponseStub $embeddedResponseStub = null
    ) {
    }

    public function getId(): string|int
    {
        return self::ID;
    }
}
