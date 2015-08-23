<?php

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
interface ServiceProxyBuilderInterface
{
    /**
     * @return ServiceProxyBuilderInterface
     */
    public function create($class);

    /**
     * @return ServiceProxyBuilderInterface
     */
    public function withCache(CacheProviderDecorator $cacheProvider);

    /**
     * @return ServiceProxyInterface|ServiceProxyCacheInterface
     */
    public function build();
}
