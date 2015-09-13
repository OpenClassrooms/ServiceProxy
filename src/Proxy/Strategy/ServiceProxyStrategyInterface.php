<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy;

use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseInterface;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface ServiceProxyStrategyInterface
{
    const METHOD_PREFIX = 'proxy_';

    const PROPERTY_PREFIX = 'proxy_';

    /**
     * @return ServiceProxyStrategyResponseInterface
     */
    public function execute(ServiceProxyStrategyRequestInterface $request);
}
