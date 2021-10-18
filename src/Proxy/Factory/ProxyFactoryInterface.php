<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Factory;

use OpenClassrooms\ServiceProxy\ServiceProxyInterface;

interface ProxyFactoryInterface
{
    public function createProxy($instance): ServiceProxyInterface;
}
