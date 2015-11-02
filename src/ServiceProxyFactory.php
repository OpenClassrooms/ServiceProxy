<?php

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactory as ProxyFactory;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactoryInterface;

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
        $proxy = $this->proxyFactory->createProxy($class);
        if ($proxy instanceof ServiceProxyCacheInterface) {
            if (null === $this->cacheProvider) {
                throw new InvalidCacheProviderException();
            }
            $proxy->proxy_setCacheProvider($this->cacheProvider);
        }

        return $proxy;
    }

    public function setCacheProvider(CacheProviderDecorator $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function setProxyFactory(ProxyFactoryInterface $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;
    }
}
