<?php

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub;

use OpenClassrooms\ServiceProxy\Attribute\Cache;

class ClassWithAnnotationOnPrivateMethod
{
    #[Cache]
    private function aMethod(): void
    {

    }
}
