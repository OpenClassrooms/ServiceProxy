<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model;

final class Event
{
    /**
     * @param array<string, mixed>|null $parameters
     */
    public function __construct(
        public readonly string $eventName,
        public readonly string $senderClassShortName,
        public readonly ?array $parameters = null,
        public readonly mixed $response = null,
        public readonly ?\Exception $exception = null,
    ) {
    }
}
