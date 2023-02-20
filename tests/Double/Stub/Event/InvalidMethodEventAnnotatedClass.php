<?php

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Annotation\Event;

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
