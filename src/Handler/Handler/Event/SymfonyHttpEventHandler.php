<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Event;

use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Model\Message\Message;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// notification over http event dispatcher
class SymfonyHttpEventHandler implements EventHandler
{
    use ConfigurableHandler;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $esbEndpoint,
        private readonly string $esbApiKey,
        private readonly SerializerInterface $asyncSerializer
    ) {
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function dispatch(Message $message): void
    {
        $this->httpClient->request('POST', $this->esbEndpoint, [
            'body' => $this->asyncSerializer->serialize($message->body, 'json'),
            'headers' => \array_merge($message->headers, [
                'x-api-key' => $this->esbApiKey,
                'Content-Type' => 'application/json',
            ]),
        ]);
    }

    public function getName(): string
    {
        return 'symfony_http';
    }
}
