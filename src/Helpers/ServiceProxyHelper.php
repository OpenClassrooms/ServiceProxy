<?php

namespace OpenClassrooms\ServiceProxy\Helpers;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactory;
use OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator\ServiceProxyGenerator;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestBuilder;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseBuilder;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\ServiceProxyCacheStrategy;
use OpenClassrooms\ServiceProxy\ServiceProxyBuilder;
use OpenClassrooms\ServiceProxy\ServiceProxyFactory;
use ProxyManager\Configuration;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
trait ServiceProxyHelper
{
    /**
     * @return ServiceProxyFactory
     */
    protected function getServiceProxyFactory(string $cacheDir)
    {
        $serviceProxyFactory = new ServiceProxyFactory();
        $configuration = new Configuration();
        $configuration->setProxiesTargetDir($cacheDir);
        $serviceProxyFactory->setProxyFactory($this->buildProxyFactory($configuration));

        return $serviceProxyFactory;
    }

    /**
     * @return \OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactoryInterface
     */
    protected function buildProxyFactory(Configuration $configuration = null)
    {
        $proxyFactory = new ProxyFactory($configuration);
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
     * @return \OpenClassrooms\ServiceProxy\ServiceProxyBuilderInterface
     */
    protected function getServiceProxyBuilder(string $cacheDir)
    {
        $serviceProxyBuilder = new ServiceProxyBuilder();
        $configuration = new Configuration();
        $configuration->setProxiesTargetDir($cacheDir);
        $serviceProxyBuilder->setProxyFactory($this->buildProxyFactory($configuration));

        return $serviceProxyBuilder;
    }
}
