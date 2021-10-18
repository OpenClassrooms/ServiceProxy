<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

interface ServiceProxyStrategyRequestBuilderInterface
{
    public function create(): ServiceProxyStrategyRequestBuilderInterface;

    public function withAnnotation($annotation): ServiceProxyStrategyRequestBuilderInterface;

    public function withClass(\ReflectionClass $class): ServiceProxyStrategyRequestBuilderInterface;

    public function withMethod(\ReflectionMethod $method): ServiceProxyStrategyRequestBuilderInterface;

    public function build(): ServiceProxyStrategyRequestInterface;
}
