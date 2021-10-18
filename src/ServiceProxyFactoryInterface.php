<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

interface ServiceProxyFactoryInterface
{
    public function createProxy(object $class): ServiceProxyInterface;
}
