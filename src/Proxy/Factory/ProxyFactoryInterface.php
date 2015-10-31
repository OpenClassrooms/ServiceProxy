<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Factory;

use OpenClassrooms\ServiceProxy\ServiceProxyInterface;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface ProxyFactoryInterface
{
    /**
     * @return ServiceProxyInterface|object
     */
    public function createProxy($instanceOrClassName);
}
