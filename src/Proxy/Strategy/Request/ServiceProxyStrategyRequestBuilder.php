<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyStrategyRequestBuilder implements ServiceProxyStrategyRequestBuilderInterface
{
    /**
     * @var ServiceProxyStrategyRequest
     */
    private $request;

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $this->request = new ServiceProxyStrategyRequest();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withAnnotation($annotation)
    {
        $this->request->annotation = $annotation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withClass(\ReflectionClass $class)
    {
        $this->request->class = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod(\ReflectionMethod $method)
    {
        $this->request->method = $method;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        return $this->request;
    }
}
