<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

class ServiceProxyStrategyResponse implements ServiceProxyStrategyResponseInterface
{
    public string $exceptionSource;

    /**
     * @var \Laminas\Code\Generator\MethodGenerator[]
     */
    public array $methods = [];

    public string $postSource;

    public string $preSource;

    /**
     * @var \Laminas\Code\Generator\PropertyGenerator[]
     */
    public array $properties = [];

    public function getPreSource(): string
    {
        return $this->preSource;
    }

    public function getPostSource(): string
    {
        return $this->postSource;
    }

    public function getExceptionSource(): string
    {
        return $this->exceptionSource;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }
}
