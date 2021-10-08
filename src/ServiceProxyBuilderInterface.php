<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;

interface ServiceProxyBuilderInterface
{
    public function create(object $class): ServiceProxyBuilderInterface;

    public function withCache(CacheProviderDecorator $cacheProvider): ServiceProxyBuilderInterface;

    public function build(): ServiceProxyInterface;
}
