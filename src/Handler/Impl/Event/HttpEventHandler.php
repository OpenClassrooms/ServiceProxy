<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Event;

use OpenClassrooms\ServiceProxy\Handler\Config\Event\HttpEventHandlerConfig;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use OpenClassrooms\ServiceProxy\Model\Event;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpEventHandler implements EventHandler
{
    use ConfigurableHandler;
    use RequestAwareMessageTrait;

    private LoggerInterface $logger;

    public function __construct(
        private readonly HttpEventHandlerConfig $config,
        private readonly HttpClientInterface $httpClient,
        private readonly SerializerInterface $serializer,
        ?LoggerInterface $logger = null,
        private readonly ?RequestStack $request = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function dispatch(Event $event, ?string $queue = null): void
    {
        $message = $this->createMessage($event, $queue);
        $response = $this->httpClient->request('POST', $this->config->brokerEndpoint, [
            'body' => $this->serializer->serialize($message, 'json'),
            'headers' => [
                ...$message->headers,
                'x-api-key' => $this->config->brokerApiKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(
                $response->getStatusCode() . ' : ' . $response->getContent(),
                compact('message', 'response')
            );
        }
    }

    public function getName(): string
    {
        return $this->name ?? 'http_async';
    }

    public function listen(Instance $instance, string $name, int $priority = 0): void
    {
        throw new \RuntimeException('Http event handler can not listen events');
    }

    private function getRequest(): ?RequestStack
    {
        return $this->request;
    }
}
