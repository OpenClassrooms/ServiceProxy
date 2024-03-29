<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl;

trait ConfigurableHandler
{
    /**
     * @var array<string, string[]>
     */
    protected array $defaultHandlers = [];

    private ?string $name = null;

    /**
     * @param array<string, string[]> $defaultHandlers
     */
    public function setDefaultHandlers(array $defaultHandlers): void
    {
        $this->defaultHandlers = $defaultHandlers;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    abstract public function getName(): string;

    public function getType(): string
    {
        $interfaces = class_implements($this);
        $interface = array_values(
            array_filter($interfaces, static fn (string $interface) => (bool) preg_match(
                '/(?<!Annotation)Handler$/',
                $interface
            ))
        )[0] ?? '';
        $interface = str_replace('Handler', '', $interface);

        return mb_strtolower(mb_substr($interface, (int) mb_strrpos($interface, '\\') + 1));
    }

    public function isDefault(): bool
    {
        $typeDefault = $this->defaultHandlers[$this->getType()] ?? null;

        return \in_array($this->getName(), $typeDefault ?? [], true);
    }
}
