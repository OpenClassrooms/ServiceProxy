<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;

final class EventHandlerMock implements EventHandler
{
    /**
     * @var array<string, object>
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

    public function dispatch(Instance $instance): void
    {
        $data = [
            ...$instance->getData(),
            'name' => $instance->getContext()?->attribute
->name,
            'type' => $instance->getContext()?->type
->value,
        ];
        $this->events[] = (object) $data;
    }

    public function isDefault(): bool
    {
        return true;
    }
}
