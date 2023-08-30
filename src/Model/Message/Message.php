<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model\Message;

final class Message
{
    /**
     * @param mixed[]|object $body
     * @param mixed[] $headers
     * @param mixed[] $context
     */
    public function __construct(
        public readonly string $name,
        public readonly array|object $body,
        public readonly array $headers = [],
    ) {
    }
}
