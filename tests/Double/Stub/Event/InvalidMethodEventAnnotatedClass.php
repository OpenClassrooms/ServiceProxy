<?php

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Annotations\Event;

class InvalidMethodEventAnnotatedClass
{
    /**
     * @Event(methods="toto")
     */
    public function eventWithWrongMethods($useCaseRequest): int
    {
        return 1;
    }
}
