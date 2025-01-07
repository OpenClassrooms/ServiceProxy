<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model;

use OpenClassrooms\ServiceProxy\Attribute\Event\Transport;
use OpenClassrooms\ServiceProxy\Model\Request\Moment;

class Event
{
    public string $name;

    /**
     * @param class-string $class
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public readonly string $class,
        public readonly string $classShortName,
        public readonly string $method,
        public readonly array $parameters,
        ?string $name = null,
        public readonly mixed $response = null,
        public readonly mixed $exception = null,
        public readonly Moment $type = Moment::SUFFIX
    ) {
        $this->name = self::getName(
            className: $class,
            moment: $type,
            transport: Transport::SYNC,
            method: $method,
            name: $name
        );
    }

    public function getUseCaseRequest(): mixed
    {
        return $this->parameters['useCaseRequest'] ?? ($this->parameters['request'] ?? null);
    }

    public function getUseCaseResponse(): mixed
    {
        return $this->response;
    }

    public function getUseCaseException(): mixed
    {
        return $this->exception;
    }

    /**
     * @param class-string $className
     */
    public static function getName(
        string $className,
        Moment $moment = Moment::SUFFIX,
        ?Transport $transport = null,
        string $method = '',
        ?string $name = null
    ): string {
        if ($name !== null) {
            return $name;
        }
        $parts = explode('\\', $className);
        $classShortName = array_pop($parts);

        $name = \in_array($method, ['__invoke', 'execute', ''], true)
            ? $classShortName
            : $classShortName . '.' . $method;

        $name = mb_strtolower((string) preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));

        if ($transport !== null && $transport !== Transport::SYNC) {
            return "{$moment->value}.{$name}.{$transport->value}";
        }

        return "{$moment->value}.{$name}";
    }
}
