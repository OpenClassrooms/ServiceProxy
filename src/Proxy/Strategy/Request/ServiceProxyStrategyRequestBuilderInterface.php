<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface ServiceProxyStrategyRequestBuilderInterface
{
    /**
     * @return ServiceProxyStrategyRequestBuilderInterface
     */
    public function create();

    /**
     * @return ServiceProxyStrategyRequestBuilderInterface
     */
    public function withAnnotation($annotation);

    /**
     * @return ServiceProxyStrategyRequestBuilderInterface
     */
    public function withClass(\ReflectionClass $class);

    /**
     * @return ServiceProxyStrategyRequestBuilderInterface
     */
    public function withMethod(\ReflectionMethod $method);

    /**
     * @return ServiceProxyStrategyRequestInterface
     */
    public function build();
}
