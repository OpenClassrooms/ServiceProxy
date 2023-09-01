<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Event;

use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Model\Message\Message;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use Symfony\Component\Messenger\MessageBusInterface;

// async over messenger event dispatcher
class SymfonyMessengerEventHandler implements EventHandler
{
    use ConfigurableHandler;

    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    public function dispatch(Message $message): void
    {
        $this->bus->dispatch($message);
    }

    public function getName(): string
    {
        return 'symfony_messenger';
    }
}
