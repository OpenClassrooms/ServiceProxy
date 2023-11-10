<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotation;

abstract class Annotation
{
    /**
     * @var array<string>|null
     */
    protected array|string|null $handler = null;

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
     * @throws \BadMethodCallException
     */
    final public function __isset(string $name): bool
    {
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }

    /**
     * @throws \BadMethodCallException
     */
    final public function __set(string $name, mixed $value): void
    {
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, static::class)
        );
    }

    /**
     * @return array<string>
     */
    final public function getHandlers(): array
    {
        return (array) ($this->handler ?? []);
    }

    /**
     * @param array<string, string>|string|null $handlers
     * @param array<string, array<string>|string|null> $aliases
     */
    final protected function setHandlers(array|string|null $handlers = null, array $aliases = []): void
    {
        if (\count($aliases) > 0) {
            $values = array_values($aliases);
            $keys = array_keys($aliases);
            if ($values[0] !== null && $values[1] !== null) {
                throw new \RuntimeException(
                    "Argument '{$keys[1]}' is an alias for '{$keys[0]}'.
                You can only define one of the two arguments."
                );
            }

            $this->handler = $values[0] ?? $values[1];
        } else {
            $this->handler = $handlers;
        }
    }
}
