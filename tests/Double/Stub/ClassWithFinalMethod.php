<?php declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub;

use OpenClassrooms\ServiceProxy\Attribute\Cache;

class ClassWithFinalMethod
{
    #[Cache]
    public final function aMethod(): void
    {

    }
}
