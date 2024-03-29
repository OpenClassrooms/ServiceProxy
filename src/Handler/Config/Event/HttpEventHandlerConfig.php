<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Config\Event;

final class HttpEventHandlerConfig
{
    public function __construct(
        public readonly string $brokerApiKey,
        public readonly string $brokerEndpoint,
    ) {
    }
}
