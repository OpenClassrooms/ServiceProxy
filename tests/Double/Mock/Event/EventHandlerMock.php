<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Model\Message\Message;

final class EventHandlerMock implements EventHandler
{
    /**
     * @var array<string, Message>
     */
    private array $events = [];

    public function getEvent(string $name, int $position = 0): Message
    {
        $events = $this->getEvents($name);

        if (!isset($events[$position])) {
            throw new \RuntimeException("Event {$name} not found at position {$position}");
        }

        return $events[$position];
    }

    /**
     * @return array<string, Message>
     */
    public function getEvents(string $name = null): array
    {
        if ($name !== null) {
            return array_values(
                array_filter(
                    $this->events,
                    static fn (Message $event) => $event->name === $name
                )
            );
        }

        return $this->events;
    }

    public function getName(): string
    {
        return 'array';
    }

    public function dispatch(Message $message): void
    {
        $this->events[] = $message;
    }

    public function isDefault(): bool
    {
        return true;
    }
}
