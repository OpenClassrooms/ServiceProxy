<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

use OpenClassrooms\ServiceProxy\Annotations\Cache;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyStrategyRequest implements ServiceProxyStrategyRequestInterface
{
    /**
     * @var Cache
     */
    public $annotation;

    /**
     * @var \ReflectionClass
     */
    public $class;

    /**
     * @var \ReflectionMethod
     */
    public $method;

    /**
     * {@inheritdoc}
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }
}
