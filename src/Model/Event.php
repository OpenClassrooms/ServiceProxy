<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model;

use OpenClassrooms\ServiceProxy\Attribute\Event\Transport;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Request\Moment;

final class Event
{
    /**
     * @param mixed[] $parameters
     */
    public function __construct(
        public readonly string $name,
        public readonly string $class,
        public readonly string $classShortName,
        public readonly string $method,
        public readonly array $parameters,
        public readonly mixed $response = null,
        public readonly mixed $exception = null,
        public readonly Moment $type = Moment::SUFFIX
    ) {
    }

    public static function createFromSenderInstance(
        Instance $instance,
        Moment $moment = Moment::SUFFIX,
        ?string $name = null
    ): self {
        /** @var class-string $className */
        $className = $instance->getReflection()->getName();

        return new self(
            self::getName(
                className: $className,
                moment: $moment,
                method: $instance->getMethod()
                    ->getName(),
                name: $name,
            ),
            $instance->getReflection()
                ->getName(),
            $instance->getReflection()
                ->getShortName(),
            $instance->getMethod()
                ->getName(),
            $instance->getMethod()
                ->getParameters(),
            $instance->getMethod()
                ->getReturnedValue(),
            $instance->getMethod()
                ->getException(),
            $moment,
        );
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

        if ($transport !== null) {
            return "{$moment->value}.{$name}.{$transport->value}";
        }

        return "{$moment->value}.{$name}";
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
}
