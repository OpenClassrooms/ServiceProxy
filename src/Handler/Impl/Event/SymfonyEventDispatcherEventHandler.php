<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SymfonyEventDispatcherEventHandler implements EventHandler
{
    use ConfigurableHandler;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function dispatch(Instance $instance): void
    {
        $this->eventDispatcher->dispatch(
            $instance->getEvent(),
            $this->getEventName($instance)
        );
    }

    public function getName(): string
    {
        return 'symfony_event_dispatcher';
    }

    public function listen(Instance $instance): void
    {
        $this->eventDispatcher->addListener(
            $instance->getMethod()
                ->getAttribute(Event\Listen::class)->name,
            $this->getCallable($instance)
        );
    }

    private function getEventName(Instance $instance): string
    {
        $attribute = $instance->getContext()?->attribute;
        if ($attribute instanceof Event && $attribute->name !== null) {
            return $attribute->name;
        }

        $name = $instance->getReflection()
            ->getShortName()
        ;
        if (\in_array($instance->getMethod()->getName(), ['__invoke', 'execute'], true)) {
            $name = $instance->getMethod()
                ->getName() . '.' . $name;
        }
        $name = mb_strtolower((string) preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));
        $type = $instance->getContext()?->type?->value;

        return "{$type}.{$name}";
    }

    private function getCallable(Instance $instance): callable
    {
        return function (\OpenClassrooms\ServiceProxy\Model\Event $event) use ($instance) {
            $methodRef = $instance->getMethod()
                ->getReflection()
            ;
            $args = $this->guessArgs($methodRef, $event);
            $methodRef->invoke($instance->getObject(), $args);
        };
    }

    /**
     * @return array<mixed>
     */
    private function guessArgs(
        \ReflectionMethod                        $method,
        \OpenClassrooms\ServiceProxy\Model\Event $event,
    ): array {
        $params = $method->getParameters();

        return [...$event->parameters, ...$params];
    }
}
