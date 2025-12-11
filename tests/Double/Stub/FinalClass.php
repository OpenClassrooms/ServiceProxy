<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub;

use OpenClassrooms\ServiceProxy\Attribute\Cache;

final class FinalClass
{
    #[Cache]
    public function aMethod(): void
    {
    }
}
