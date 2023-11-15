<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

use OpenClassrooms\ServiceProxy\Interceptor\Contract\Cache\AutoTaggable;

class EmbeddedResponseStub implements AutoTaggable
{
    public const ID = 42;

    private $id = self::ID;

    public function getId(): string|int
    {
        return $this->id;
    }
}
