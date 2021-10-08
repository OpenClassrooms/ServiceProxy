<?php

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;

interface ServiceProxyCacheInterface
{
    public function proxy_setCacheProvider(CacheProviderDecorator $cacheProvider);
}
