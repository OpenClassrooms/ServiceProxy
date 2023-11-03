<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model;

final class MessageContext
{
    public function __construct(
        public readonly string $subject,
        public readonly string $state
    ) {
    }
}
