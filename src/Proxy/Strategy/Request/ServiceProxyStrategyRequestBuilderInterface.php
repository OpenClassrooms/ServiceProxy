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
     * @return ServiceProxyStrategyRequestInterface
     */
    public function build();
}
