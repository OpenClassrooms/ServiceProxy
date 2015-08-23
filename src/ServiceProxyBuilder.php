<?php

namespace OpenClassrooms\ServiceProxy;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;

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
     * @var mixed
     */
    private $class;

    /**
     * @var ServiceProxyFactoryInterface
     */
    private $serviceProxyFactory;

    /**
     * @inheritdoc
     */
    public function create($class)
    {
        $this->class = $class;
        $this->cacheProvider = null;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withCache(CacheProviderDecorator $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        $proxy = $this->serviceProxyFactory->createSimpleProxy($this->class);
        if ($proxy instanceof ServiceProxyCacheInterface) {
            $proxy->setCacheProvider($this->cacheProvider);
        }

        return $proxy;
    }

    public function setServiceProxyFactory(ServiceProxyFactoryInterface $serviceProxyFactory)
    {
        $this->serviceProxyFactory = $serviceProxyFactory;
    }
}
