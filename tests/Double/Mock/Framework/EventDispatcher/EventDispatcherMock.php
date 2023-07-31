<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Framework\EventDispatcher;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherMock implements EventDispatcherInterface
{
    public $dispatchedEvent = null;

    public function dispatch($event)
    {
        $this->dispatchedEvent = $event;
    }
}
