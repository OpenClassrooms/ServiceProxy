<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotation;

use OpenClassrooms\ServiceProxy\Contract\TransactionHandler;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Transaction extends Annotation
{
    public function getHandlerClass(): string
    {
        return TransactionHandler::class;
    }
}
