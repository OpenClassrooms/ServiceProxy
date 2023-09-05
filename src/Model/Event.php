<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model;

use OpenClassrooms\ServiceProxy\Interceptor\Request\ContextType;

final class Event
{
    /**
     * @param mixed[] $parameters
     */
    public function __construct(
        public readonly string $class,
        public readonly string $classShortName,
        public readonly string $method,
        public readonly array $parameters,
        public readonly mixed $response = null,
        public readonly mixed $exception = null,
        public readonly ContextType $type = ContextType::PREFIX
    ) {
    }
}
