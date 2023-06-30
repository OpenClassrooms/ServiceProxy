<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotation;

use OpenClassrooms\ServiceProxy\Handler\Contract\AnnotationHandler;

abstract class Annotation
{
    protected ?string $handler = null;

    protected int $prefixPriority = 0;

    protected int $suffixPriority = 0;

    /**
     * @param array<string, mixed> $data Key-value for properties to be defined in this class.
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (method_exists($this, 'set' . ucfirst($key))) {
                $setter = 'set' . ucfirst($key);
                // @phpstan-ignore-next-line
                $this->{$setter}($value);
            } else {
                // @phpstan-ignore-next-line
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @throws \BadMethodCallException
     */
    final public function __get(string $name): void
    {
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }

    /**
     * @param string $name Unknown property name.
     *
     * @throws \BadMethodCallException
     */
    final public function __isset(string $name): bool
    {
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }

    /**
     * @param string $name  Unknown property name.
     * @param mixed  $value Property value.
     *
     * @throws \BadMethodCallException
     */
    final public function __set(string $name, $value): void
    {
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }

    final public function getHandler(): ?string
    {
        return $this->handler;
    }

    /**
     * @return class-string<AnnotationHandler>
     */
    abstract public function getHandlerClass(): string;

    final public function getPrefixPriority(): int
    {
        return $this->prefixPriority;
    }

    final public function getSuffixPriority(): int
    {
        return $this->suffixPriority;
    }

    final public function setHandler(?string $handler): void
    {
        $this->handler = $handler;
    }

    final public function setPrefixPriority(int $prefixPriority): void
    {
        $this->prefixPriority = $prefixPriority;
    }

    final public function setSuffixPriority(int $suffixPriority): void
    {
        $this->suffixPriority = $suffixPriority;
    }
}
