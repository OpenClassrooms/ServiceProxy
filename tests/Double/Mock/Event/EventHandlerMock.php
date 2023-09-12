<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Model\Event;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;

final class EventHandlerMock implements EventHandler
{
    /**
     * @var array<\OpenClassrooms\ServiceProxy\Model\Event>
     */
    private array $events = [];

    /**
     * @return array<string, object>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function getName(): string
    {
        return 'array';
    }

    public function dispatch(Event $event, ?string $queue = null): void
    {
        $this->events[] = $event;
    }

    public function isDefault(): bool
    {
        return true;
    }

    public function setDefaultHandlers(array $defaultHandlers): void
    {
    }

    public function listen(Instance $instance, string $name, int $priority = 0): void
    {
    }
}
