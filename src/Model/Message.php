<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model;

final class Message
{
    /**
     * @param array<mixed> $headers
     */
    public function __construct(
        public readonly MessageContext $context,
        public readonly Event $body,
        public readonly array $headers = []
    ) {
    }

    public function getName(): string
    {
        return $this->body->name ?? $this->context->subject . '_' . $this->context->state;
    }
}
