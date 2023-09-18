<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event\Transport;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Model\Event;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyMessengerEventHandler implements EventHandler
{
    use ConfigurableHandler;
    use RequestAwareMessageTrait;

    private LoggerInterface $logger;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ?RequestStack       $request = null,
        ?LoggerInterface                     $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function dispatch(Event $event, ?string $queue = null): void
    {
        $message = $this->createMessage($event, $queue);
        try {
            $this->bus->dispatch($message);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), compact('message', 'exception'));
        }
    }

    public function listen(Instance $instance, string $name, ?Transport $transport = null, int $priority = 0): void
    {
        return;
    }

    public function getName(): string
    {
        return $this->name ?? 'symfony_messenger';
    }

    private function getRequest(): ?RequestStack
    {
        return $this->request;
    }
}
