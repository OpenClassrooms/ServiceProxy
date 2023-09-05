<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotation;

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

    final public function getPrefixPriority(): int
    {
        return $this->prefixPriority;
    }

    final public function getSuffixPriority(): int
    {
        return $this->suffixPriority;
    }

    final public function setPrefixPriority(int $prefixPriority): void
    {
        $this->prefixPriority = $prefixPriority;
    }

    final public function setSuffixPriority(int $suffixPriority): void
    {
        $this->suffixPriority = $suffixPriority;
    }

    /**
     * @param array<string, null|string> $aliases
     */
    final protected function setHandler(?string $handler = null, array $aliases = []): void
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
            $this->handler = $handler;
        }
    }
}
