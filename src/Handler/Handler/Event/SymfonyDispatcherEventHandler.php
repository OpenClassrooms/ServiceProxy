<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Model\Message\Message;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


final class SymfonyDispatcherEventHandler implements EventHandler
{
    use ConfigurableHandler;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatch(Message $message): void
    {
        $this->eventDispatcher->dispatch($message);
    }

    public function getName(): string
    {
        return 'symfony_event_dispatcher';
    }
}
