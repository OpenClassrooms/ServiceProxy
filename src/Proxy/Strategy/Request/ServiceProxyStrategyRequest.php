<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

use OpenClassrooms\ServiceProxy\Annotations\ServiceProxyAnnotation;

class ServiceProxyStrategyRequest implements ServiceProxyStrategyRequestInterface
{
    public ServiceProxyAnnotation $annotation;

    public \ReflectionClass $class;

    public \ReflectionMethod $method;

    public function getAnnotation(): ServiceProxyAnnotation
    {
        return $this->annotation;
    }

    public function getClass(): \ReflectionClass
    {
        return $this->class;
    }

    public function getMethod(): \ReflectionMethod
    {
        return $this->method;
    }
}
