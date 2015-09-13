<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

use OpenClassrooms\ServiceProxy\Annotations\Cache;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface ServiceProxyStrategyRequestInterface
{
    /**
     * @return Cache
     */
    public function getAnnotation();

    /**
     * @return \ReflectionClass
     */
    public function getClass();

    /**
     * @return \ReflectionMethod
     */
    public function getMethod();
}
