<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event;

class InvalidResponseMessageClassAnnotatedClass
{
    #[Event(messageClass: CustomMessage::class)]
    public function invalid(string $value): int
    {
        return 42;
    }
}
