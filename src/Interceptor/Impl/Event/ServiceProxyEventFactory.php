<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl\Event;

use OpenClassrooms\ServiceProxy\Interceptor\Contract\Event\EventFactory;
use OpenClassrooms\ServiceProxy\Model\Event;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Request\Moment;

final class ServiceProxyEventFactory implements EventFactory
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
    ): Event {
        return new $eventClassName(
            $instance->getReflection()->getName(),
            $instance->getReflection()->getShortName(),
            $instance->getMethod()->getName(),
            $instance->getMethod()->getParameters(),
            $name,
            $instance->getMethod()->getReturnedValue(),
            $instance->getMethod()->getException(),
            $moment,
        );
    }
}
