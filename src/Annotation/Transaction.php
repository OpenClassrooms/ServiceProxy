<?php

namespace OpenClassrooms\ServiceProxy\Annotation;

use OpenClassrooms\ServiceProxy\Contract\TransactionHandler;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Transaction extends Annotation
{
    public function getHandlerClass(): string
    {
        return TransactionHandler::class;
    }
}
