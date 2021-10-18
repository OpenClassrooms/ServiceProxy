<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

use OpenClassrooms\ServiceProxy\Annotations\Cache;

class ServiceProxyStrategyRequest implements ServiceProxyStrategyRequestInterface
{
    /**
     * @var Cache
     */
    public $annotation;

    public \ReflectionClass $class;

    public \ReflectionMethod $method;

    /**
     * {@inheritdoc}
     */
    public function getAnnotation()
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
