<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Model\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @implements EventHandler<Event>
 */
final class SymfonyDispatcherEventHandler implements EventHandler
{
    use ConfigurableHandler;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function send(object $event): void
    {
        $this->eventDispatcher->dispatch($event);
    }

    public function getName(): string
    {
        return 'symfony_event_dispatcher';
    }

    public function make(
        string $eventName,
        string $senderClassShortName,
        ?array $parameters = null,
        $response = null,
        \Exception $exception = null
    ): Event {
        return new Event($eventName, $senderClassShortName, $parameters, $response, $exception);
    }
}
