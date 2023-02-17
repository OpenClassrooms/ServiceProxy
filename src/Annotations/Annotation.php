<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotations;

use BadMethodCallException;

abstract class Annotation
{
    private ?string $handler = null;

    /**
     * @param array<string, mixed> $data Key-value for properties to be defined in this class.
     */
    final public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (method_exists($this, "set" . ucfirst($key))) {
                $this->{"set" . ucfirst($key)}($value);
            } else {
                $this->$key = $value;
            }
        }
    }

    /**
     * @throws BadMethodCallException
     */
    public function __get(string $name)
    {
        throw new BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }

    /**
     * @param string $name Unknown property name.
     *
     * @throws BadMethodCallException
     */
    public function __isset(string $name)
    {
        throw new BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }

    /**
     * @param string $name  Unknown property name.
     * @param mixed  $value Property value.
     *
     * @throws BadMethodCallException
     */
    public function __set(string $name, $value)
    {
        throw new BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    /**
     * @return class-string<\OpenClassrooms\ServiceProxy\Contract\AnnotationHandler>
     */
    abstract public function getHandlerClass(): string;

    public function setHandler(?string $handler): void
    {
        $this->handler = $handler;
    }
}
