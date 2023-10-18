<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\Event\SymfonyEventDispatcherEventHandler;
use OpenClassrooms\ServiceProxy\Model\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

final class EventHandlerMock implements EventHandler
{
    private array $events = [];

    private EventHandler $decoratedEventHandler;

    private TraceableEventDispatcher $eventDispatcher;

    public function __construct()
    {
        $this->eventDispatcher = new TraceableEventDispatcher(
            new EventDispatcher(),
            new Stopwatch()
        );

        $this->decoratedEventHandler = new SymfonyEventDispatcherEventHandler(
            $this->eventDispatcher
        );
    }

    public function getCreatedEvent(string $name, int $position = 0): Event
    {
        $events = $this->getCreatedEvents($name);

        if (!isset($events[$position])) {
            throw new \RuntimeException("Event {$name} not found at position {$position}");
        }

        return $events[$position];
    }

    /**
     * @return array<string, Event>
     */
    public function getCreatedEvents(string $name = null): array
    {
        if ($name !== null) {
            return array_values(
                array_filter(
                    $this->events,
                    static fn (Event $event) => $event->eventName === $name
                )
            );
        }

        return $this->events;
    }

    public function getSentEvents(): array
    {
        return $this->eventDispatcher->getOrphanedEvents();
    }

    public function getName(): string
    {
        return 'array';
    }

    public function make(
        $eventName,
        string $senderClassShortName,
        ?array $parameters = null,
        $response = null,
        \Exception $exception = null
    ): Event {
        $event = $this->decoratedEventHandler->make(
            $eventName,
            $senderClassShortName,
            $parameters,
            $response,
            $exception
        );

        $this->events[] = $event;

        return $event;
    }

    /**
     * @param Event $event
     */
    public function send(object $event): void
    {
        $this->decoratedEventHandler->send($event);
    }

    public function isDefault(): bool
    {
        return true;
    }

    public function setDefaultHandlers(array $defaultHandlers): void
    {
    }
}
