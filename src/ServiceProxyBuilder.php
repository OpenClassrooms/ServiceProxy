<?php

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactoryInterface;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyBuilder implements ServiceProxyBuilderInterface
{
    /**
     * @var CacheProviderDecorator
     */
    private $cacheProvider;

    /**
     * @var object
     */
    private $class;

    /**
     * @var ProxyFactoryInterface
     */
    private $proxyFactory;

    /**
     * {@inheritdoc}
     */
    public function create($class)
    {
        $this->class = $class;
        $this->cacheProvider = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withCache(CacheProviderDecorator $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build()
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

    public function setProxyFactory(ProxyFactoryInterface $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;
    }
}
