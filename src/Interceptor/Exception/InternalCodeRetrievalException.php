<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Exception;

final class InternalCodeRetrievalException extends \RuntimeException
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf(
                'Unable to retrieve code for method or class "%s".',
                $name,
            )
        );
    }
}
