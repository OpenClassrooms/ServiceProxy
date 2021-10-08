<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Response;

interface ServiceProxyStrategyResponseBuilderInterface
{
    public function create(): ServiceProxyStrategyResponseBuilderInterface;

    public function withExceptionSource(string $exceptionSource): ServiceProxyStrategyResponseBuilderInterface;

    public function withMethods(array $methods): ServiceProxyStrategyResponseBuilderInterface;

    public function withPostSource(string $postSource): ServiceProxyStrategyResponseBuilderInterface;

    public function withPreSource(string $preSource): ServiceProxyStrategyResponseBuilderInterface;

    public function withProperties(array $properties): ServiceProxyStrategyResponseBuilderInterface;

    public function build(): ServiceProxyStrategyResponseInterface;
}
