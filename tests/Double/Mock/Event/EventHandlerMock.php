<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event;

use OpenClassrooms\ServiceProxy\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event\EventStub;

final class EventHandlerMock implements EventHandler
{
    private array $events = [];

    public function getEvent(string $name, int $position = 0): EventStub
    {
        $events = $this->getEvents($name);

        if (!isset($events[$position])) {
            throw new \RuntimeException("Event {$name} not found at position {$position}");
        }

        return $events[$position];
    }

    /**
     * @return array<string, EventStub>
     */
    public function getEvents(string $name = null): array
    {
        if ($name !== null) {
            return array_values(
                array_filter(
                    $this->events,
                    static function (EventStub $event) use ($name) {
                        return $event->getName() === $name;
                    }
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
        ?array $parameters = null,
        $response = null,
        \Exception $exception = null
    ): EventStub {
        return new EventStub($eventName, compact('parameters', 'response', 'exception'));
    }

    /**
     * @param EventStub $event
     */
    public function send($event): void
    {
        $this->events[] = $event;
    }

    public function isDefault(): bool
    {
        return true;
    }
}
