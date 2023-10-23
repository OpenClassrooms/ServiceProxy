<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache;

class ResponseStub
{
    public const ID = 12;

    private $id = self::ID;

    public function __construct(
        private ?EmbeddedResponseStub $embeddedResponseStub = null
    ) {
    }
}
