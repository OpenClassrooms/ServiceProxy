<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event;

class ClassWithInvalidEventAttributes
{
    #[Event(methods: 'invalid')]
    public function eventWithWrongMethods($useCaseRequest): int
    {
        return 1;
    }
}
