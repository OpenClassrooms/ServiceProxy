<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Event;

use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyMessengerEventHandler implements EventHandler
{
    use ConfigurableHandler;

    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    public function dispatch(Instance $instance): void
    {
        $this->bus->dispatch($instance->getEvent());
    }

    public function listen(Instance $instance): void
    {
        //todo implement this
    }

    public function getName(): string
    {
        return 'symfony_messenger';
    }
}
