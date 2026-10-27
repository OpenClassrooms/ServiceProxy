<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Attribute;

use OpenClassrooms\ServiceProxy\Attribute\Event;
use OpenClassrooms\ServiceProxy\Attribute\Event\On;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class EventTest extends TestCase
{
    public function testAcceptsEventDispatchEnums(): void
    {
        $event = new Event(dispatch: [On::PRE, On::POST, On::EXCEPTION]);

        $this->assertTrue($event->isPre());
        $this->assertTrue($event->isPost());
        $this->assertTrue($event->isOnException());
    }

    public function testRejectsInvalidEventDispatchValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Event(dispatch: ['post']);
    }
}
