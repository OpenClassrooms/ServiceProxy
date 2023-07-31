<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Model\GenericEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @implements EventHandler<Event>
 */
final class GenericEventHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
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
        string $senderClassShortName,
        ?array $parameters = null,
        mixed $response = null,
    ): GenericEvent {
        return new GenericEvent(
            $senderClassShortName,
            $parameters,
            $response,
        );
    }
}
