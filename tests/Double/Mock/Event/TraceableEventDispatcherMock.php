<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event;

use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceableEventDispatcherMock extends TraceableEventDispatcher
{
    public function __construct()
    {
        parent::__construct(new EventDispatcher(), new Stopwatch());
    }
}
