<?php

namespace OpenClassrooms\ServiceProxy\Annotations;

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
