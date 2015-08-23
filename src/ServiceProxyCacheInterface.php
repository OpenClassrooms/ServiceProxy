<?php

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface ServiceProxyCacheInterface
{
    public function setCacheProvider(CacheProviderDecorator $cacheProvider);
}
