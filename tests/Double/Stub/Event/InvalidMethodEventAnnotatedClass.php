<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event;

class InvalidMethodEventAnnotatedClass
{
    #[Event(dispatch: ['toto'])]
    public function eventWithWrongMethods(): void
    {
    }
}
