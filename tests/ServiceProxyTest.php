<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactory as ProxyFactory;
use OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator\ServiceProxyGenerator;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestBuilder;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseBuilder;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\ServiceProxyCacheStrategy;
use OpenClassrooms\ServiceProxy\ServiceProxyBuilder;
use OpenClassrooms\ServiceProxy\ServiceProxyBuilderInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyFactory;
use OpenClassrooms\ServiceProxy\ServiceProxyFactoryInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use PHPUnit_Framework_Assert as Assert;
use ProxyManager\Factory\AbstractBaseFactory;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
trait ServiceProxyTest
{
    /**
     * @return ServiceProxyBuilderInterface
     */
    protected function buildServiceProxyBuilder()
    {
        $builder = new ServiceProxyBuilder();
        $builder->setServiceProxyFactory($this->buildServiceProxyFactory());

        return $builder;
    }

    /**
     * @return ServiceProxyFactoryInterface
     */
    protected function buildServiceProxyFactory()
    {
        $serviceProxyFactory = new ServiceProxyFactory();
        $serviceProxyFactory->setProxyFactory($this->buildProxyFactory());
        $serviceProxyFactory->setCacheProvider(new CacheProviderDecorator(new ArrayCache()));

        return $serviceProxyFactory;
    }

    /**
     * @return AbstractBaseFactory
     */
    private function buildProxyFactory()
    {
        $proxyFactory = new ProxyFactory();
        $proxyFactory->setGenerator($this->buildGenerator());

        return $proxyFactory;
    }

    /**
     * @return ServiceProxyGenerator
     */
    private function buildGenerator()
    {
        $generator = new ServiceProxyGenerator();
        $generator->setAnnotationReader(new AnnotationReader());
        $generator->setCacheStrategy($this->buildCacheStrategy());
        $generator->setServiceProxyStrategyRequestBuilder(new ServiceProxyStrategyRequestBuilder());

        return $generator;
    }

    /**
     * @return ServiceProxyCacheStrategy
     */
    private function buildCacheStrategy()
    {
        $cacheStrategy = new ServiceProxyCacheStrategy();
        $cacheStrategy->setServiceProxyStrategyResponseBuilder(new ServiceProxyStrategyResponseBuilder());

        return $cacheStrategy;
    }

    /**
     * @param ServiceProxyInterface|ServiceProxyCacheInterface $proxy
     */
    protected function assertServiceCacheProxy($inputClass, ServiceProxyCacheInterface $proxy)
    {
        $this->assertProxy($inputClass, $proxy);
        Assert::assertInstanceOf('OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface', $proxy);
        Assert::assertAttributeInstanceOf(
            'OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator',
            'proxy_cacheProvider',
            $proxy
        );
    }

    protected function assertProxy($inputClass, ServiceProxyInterface $proxy)
    {
        Assert::assertInstanceOf(get_class($inputClass), $proxy);
        Assert::assertInstanceOf('OpenClassrooms\ServiceProxy\ServiceProxyInterface', $proxy);
    }
}
