<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

class ServiceProxyStrategyRequestBuilder implements ServiceProxyStrategyRequestBuilderInterface
{
    private ServiceProxyStrategyRequest $request;

    public function create(): ServiceProxyStrategyRequestBuilderInterface
    {
        $this->request = new ServiceProxyStrategyRequest();

        return $this;
    }

    public function withAnnotation($annotation): ServiceProxyStrategyRequestBuilderInterface
    {
        $this->request->annotation = $annotation;

        return $this;
    }

    public function withClass(\ReflectionClass $class): ServiceProxyStrategyRequestBuilderInterface
    {
        $this->request->class = $class;

        return $this;
    }

    public function withMethod(\ReflectionMethod $method): ServiceProxyStrategyRequestBuilderInterface
    {
        $this->request->method = $method;

        return $this;
    }

    public function build(): ServiceProxyStrategyRequestInterface
    {
        return $this->request;
    }
}
