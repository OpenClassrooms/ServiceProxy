<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactoryInterface;

class ServiceProxyBuilder implements ServiceProxyBuilderInterface
{
    private ?CacheProviderDecorator $cacheProvider = null;

    private object $class;

    private ProxyFactoryInterface $proxyFactory;

    public function create(object $class): ServiceProxyBuilderInterface
    {
        $this->class = $class;
        $this->cacheProvider = null;

        return $this;
    }

    public function withCache(CacheProviderDecorator $cacheProvider): ServiceProxyBuilderInterface
    {
        $this->cacheProvider = $cacheProvider;

        return $this;
    }

    /**
     * @throws \OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException
     */
    public function build(): ServiceProxyInterface
    {
        $proxy = $this->proxyFactory->createProxy($this->class);

        if ($proxy instanceof ServiceProxyCacheInterface) {
            if (null === $this->cacheProvider) {
                throw new InvalidCacheProviderException();
            }
            $proxy->proxy_setCacheProvider($this->cacheProvider);
        }

        return $proxy;
    }

    public function setProxyFactory(ProxyFactoryInterface $proxyFactory): void
    {
        $this->proxyFactory = $proxyFactory;
    }
}
