<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Strategy\Request;

use OpenClassrooms\ServiceProxy\Annotations\ServiceProxyAnnotation;

interface ServiceProxyStrategyRequestInterface
{
    public function getAnnotation(): ServiceProxyAnnotation;

    public function getClass(): \ReflectionClass;

    public function getMethod(): \ReflectionMethod;
}
