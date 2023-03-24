<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotation;

use OpenClassrooms\ServiceProxy\Handler\Contract\TransactionHandler;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Transaction extends Annotation
{
    /**
     * @var array<class-string, class-string>
     */
    private array $exceptions = [];

    /**
     * @return array<class-string, class-string>
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @param array<class-string, class-string> $exceptions
     */
    public function setExceptions(array $exceptions): void
    {
        $this->exceptions = $exceptions;
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
