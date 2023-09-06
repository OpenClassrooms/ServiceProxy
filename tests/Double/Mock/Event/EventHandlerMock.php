<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\Event\SymfonyDispatcherEventHandler;
use OpenClassrooms\ServiceProxy\Model\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class EventHandlerMock implements EventHandler
{
    private array $events = [];

    private EventDispatcherInterface $eventDispatcher;

    public function __construct()
    {
        $this->eventDispatcher = new TraceableEventDispatcherMock();
    }

    public function getEvent(string $name, int $position = 0): Event
    {
        $events = $this->getEvents($name);

        if (!isset($events[$position])) {
            throw new \RuntimeException("Event {$name} not found at position {$position}");
        }

        return $events[$position];
    }

    /**
     * @return array<string, Event>
     */
    public function getEvents(string $name = null): array
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
        return new Event($eventName, $senderClassShortName, $parameters, $response, $exception);
    }

    /**
     * @param Event $event
     */
    public function send(object $event): void
    {
        $symfonyDispatcherEventHandler = new SymfonyDispatcherEventHandler($this->eventDispatcher);
        $symfonyDispatcherEventHandler->send($event);
        $this->events[] = $event;
    }

    public function getOrphanedEvents(): array
    {
        return $this->eventDispatcher->getOrphanedEvents();
    }

    public function addListener(string $eventName, callable $listener): void
    {
        $this->eventDispatcher->addListener($eventName, $listener);
    }

    public function isDefault(): bool
    {
        return true;
    }
}
