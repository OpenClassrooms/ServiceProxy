<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model;

use Symfony\Contracts\EventDispatcher\Event;

final class GenericEvent extends Event
{
    public readonly string $eventName;

    public function __construct(
        public readonly string $senderClassShortName,
        public readonly ?array $parameters = null,
        public readonly mixed $response = null,
    ) {
        $this->eventName = 'use_case.post.execute';
    }

    public function getData(): array
    {
        $parameters = $this->parameters;
        $response = $this->response;
        return compact('parameters', 'response');
    }
}
