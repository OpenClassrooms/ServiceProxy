<?php

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactory as ProxyFactory;
use ProxyManager\Factory\AbstractBaseFactory;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyFactory implements ServiceProxyFactoryInterface
{
    /**
     * @var CacheProviderDecorator
     */
    private $cacheProvider;

    /**
     * @var ProxyFactory
     */
    private $proxyFactory;

    /**
     * {@inheritdoc}
     */
    public function createProxy($class)
    {
        $proxy = $this->createSimpleProxy($class);

        if ($proxy instanceof ServiceProxyCacheInterface) {
            $proxy->proxy_setCacheProvider($this->cacheProvider);
        }

        return $proxy;
    }

    /**
     * {@inheritdoc}
     */
    public function createSimpleProxy($class)
    {
        return $this->proxyFactory->createProxy($class);
    }

    public function setCacheProvider(CacheProviderDecorator $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function setProxyFactory(AbstractBaseFactory $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;
    }
}
