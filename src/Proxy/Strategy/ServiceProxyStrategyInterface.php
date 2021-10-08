<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy;

use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseInterface;

interface ServiceProxyStrategyInterface
{
    public const METHOD_PREFIX = 'proxy_';

    public const PROPERTY_PREFIX = 'proxy_';

    public function execute(ServiceProxyStrategyRequestInterface $request): ServiceProxyStrategyResponseInterface;
}
