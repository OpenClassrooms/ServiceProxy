<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactory;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactoryInterface;

class ServiceProxyFactory implements ServiceProxyFactoryInterface
{
    private ?CacheProviderDecorator $cacheProvider = null;

    private ProxyFactory $proxyFactory;

    /**
     * @throws \OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException
     */
    public function createProxy(object $class): ServiceProxyInterface
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

    public function setCacheProvider(CacheProviderDecorator $cacheProvider): void
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function setProxyFactory(ProxyFactoryInterface $proxyFactory): void
    {
        $this->proxyFactory = $proxyFactory;
    }
}
