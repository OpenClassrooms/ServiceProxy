<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

class ServiceProxyStrategyResponseBuilder implements ServiceProxyStrategyResponseBuilderInterface
{
    public ServiceProxyStrategyResponse $response;

    public function create(): ServiceProxyStrategyResponseBuilderInterface
    {
        $this->response = new ServiceProxyStrategyResponse();

        return $this;
    }

    public function withExceptionSource(string $exceptionSource): ServiceProxyStrategyResponseBuilderInterface
    {
        $this->response->exceptionSource = $exceptionSource;

        return $this;
    }

    public function withMethods(array $methods): ServiceProxyStrategyResponseBuilderInterface
    {
        $this->response->methods = $methods;

        return $this;
    }

    public function withPostSource(string $postSource): ServiceProxyStrategyResponseBuilderInterface
    {
        $this->response->postSource = $postSource;

        return $this;
    }

    public function withPreSource(string $preSource): ServiceProxyStrategyResponseBuilderInterface
    {
        $this->response->preSource = $preSource;

        return $this;
    }

    public function withProperties(array $properties): ServiceProxyStrategyResponseBuilderInterface
    {
        $this->response->properties = $properties;

        return $this;
    }

    public function build(): ServiceProxyStrategyResponseInterface
    {
        return $this->response;
    }
}
