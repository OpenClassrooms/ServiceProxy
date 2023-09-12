<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model;

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
        Moment $type = Moment::SUFFIX,
        ?string $name = null
    ): self {
        return new self(
            self::getEventName(
                $instance->getReflection()
                    ->getShortName(),
                $instance->getMethod()
                    ->getName(),
                $type,
                $name,
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
            $type,
        );
    }

    public static function getEventName(
        string $classShortName,
        string $method,
        Moment $type,
        ?string $name = null
    ): string {
        if ($name !== null) {
            return $name;
        }

        $name = \in_array($method, ['__invoke', 'execute'], true)
            ? $classShortName
            : $classShortName . '.' . $method
        ;

        $name = mb_strtolower((string) preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));

        return "{$type->value}.{$name}";
    }
}
