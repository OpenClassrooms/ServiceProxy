<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract\Event;

use OpenClassrooms\ServiceProxy\Model\Event;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Request\Moment;

interface EventFactory
{
    /**
     * @template T of Event
     * @param class-string<T> $eventClassName
     * @return T
     */
    public function createFromSenderInstance(
        Instance $instance,
        Moment $moment = Moment::SUFFIX,
        ?string $name = null,
        string $eventClassName = Event::class
    ): Event;
}
