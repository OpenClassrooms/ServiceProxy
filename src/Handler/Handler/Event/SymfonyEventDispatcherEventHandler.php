<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SymfonyEventDispatcherEventHandler implements EventHandler
{
    use ConfigurableHandler;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatch(Instance $instance): void
    {
        $this->eventDispatcher->dispatch(
            (object) $instance->getData(),
            $this->getEventName($instance)
        );
    }

    public function getName(): string
    {
        return 'symfony_event_dispatcher';
    }

    private function getEventName(Instance $instance): string
    {
        $attribute = $instance->getContext()?->attribute;
        if ($attribute instanceof Event && $attribute->name !== null) {
            return $attribute->name;
        }

        $name = $instance->getReflection()
            ->getShortName();
        if (\in_array($instance->getMethod()->getName(), ['__invoke', 'execute'], true)) {
            $name = $instance->getMethod()
                ->getName() . '.' . $name;
        }
        $name = mb_strtolower((string) preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));
        $type = $instance->getContext()?->type?->value;

        return "{$type}.{$name}";
    }
}
