<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event\Transport;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;

final class EventHandlerMock implements EventHandler
{
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

    public function dispatch(object $event, ?string $queue = null): void
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

    public function listen(Instance $instance, string $name, ?Transport $transport = null, int $priority = 0): void
    {
    }
}
