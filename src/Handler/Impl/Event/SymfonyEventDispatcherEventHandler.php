<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Invoker\Impl\AggregateMethodInvoker;
use OpenClassrooms\ServiceProxy\Model\Event;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SymfonyEventDispatcherEventHandler implements EventHandler
{
    use ConfigurableHandler;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AggregateMethodInvoker $aggregateMethodInvoker,
    ) {
    }

    public function dispatch(Event $event, ?string $queue = null): void
    {
        $this->eventDispatcher->dispatch(
            $event,
            $event->name,
        );
    }

    public function getName(): string
    {
        return $this->name ?? 'symfony_event_dispatcher';
    }

    public function listen(Instance $instance, string $name, int $priority = 0): void
    {
        $this->eventDispatcher->addListener(
            $name,
            $this->getCallable($instance),
            $priority,
        );
    }

    private function getCallable(Instance $instance): callable
    {
        return fn (object $event) => $this->aggregateMethodInvoker->invoke($instance, $event);
    }
}
