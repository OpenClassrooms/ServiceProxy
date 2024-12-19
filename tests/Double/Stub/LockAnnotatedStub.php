<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub;

use OpenClassrooms\ServiceProxy\Attribute\Lock;

class LockAnnotatedStub
{
    #[Lock(key: '"key1"')]
    public function execute1(): void
    {
        $this->subExecute1();
    }

    #[Lock(key: '"key1"')]
    public function subExecute1(): void
    {
    }

    #[Lock(key: '"key2" ~ param1 ~ param2')]
    public function execute2(string $param1, string $param2): void
    {
    }
}
