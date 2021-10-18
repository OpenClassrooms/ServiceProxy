<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

use OpenClassrooms\ServiceProxy\Annotations\Cache;

interface ServiceProxyStrategyRequestInterface
{
    /**
     * @return Cache
     */
    public function getAnnotation();

    public function getClass(): \ReflectionClass;

    public function getMethod(): \ReflectionMethod;
}
