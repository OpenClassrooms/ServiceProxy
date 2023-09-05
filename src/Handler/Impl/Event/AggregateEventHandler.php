<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\AnnotationHandler;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;

final class AggregateEventHandler implements EventHandler
{
    use ConfigurableHandler;

    /**
     * @param iterable<AnnotationHandler> $handlers
     */
    public function __construct(
        private readonly iterable $handlers,
    ) {
    }

    public function getName(): string
    {
        return 'aggregate';
    }

    public function dispatch(Instance $instance): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof EventHandler) {
                $handler->dispatch($instance);
            }
        }
    }

    public function listen(Instance $instance): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof EventHandler) {
                $handler->listen($instance);
            }
        }
    }
}
