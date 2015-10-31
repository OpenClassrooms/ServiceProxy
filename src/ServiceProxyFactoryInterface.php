<?php

namespace OpenClassrooms\ServiceProxy;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface ServiceProxyFactoryInterface
{
    /**
     * @return ServiceProxyInterface|ServiceProxyCacheInterface
     */
    public function createProxy($class);
}
