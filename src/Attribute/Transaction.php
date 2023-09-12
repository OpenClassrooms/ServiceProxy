<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

use OpenClassrooms\ServiceProxy\Handler\Contract\TransactionHandler;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Transaction extends Attribute
{
    /**
     * @param array<class-string, class-string> $exceptions
     */
    public function __construct(
        public readonly array $exceptions
    ) {
        parent::__construct();
    }

    public function hasMappedExceptions(): bool
    {
        return \count($this->exceptions) > 0;
    }

    public function getHandlerClass(): string
    {
        return TransactionHandler::class;
    }
}
