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
     * @inheritDoc
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }
}
