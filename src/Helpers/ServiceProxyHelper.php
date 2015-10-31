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

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
trait ServiceProxyHelper
{
    /**
     * @return ServiceProxyFactory
     */
    public function getServiceProxyFactory($cacheDir = null)
    {
        $serviceProxyFactory = new ServiceProxyFactory();
        $serviceProxyFactory->setProxyFactory($this->buildProxyFactory($cacheDir));

        return $serviceProxyFactory;
    }

    /**
     * @return \OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactoryInterface
     */
    protected function buildProxyFactory($cacheDir = null)
    {
        $proxyFactory = new ProxyFactory($cacheDir);
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
    public function getServiceProxyBuilder($cacheDir = null)
    {
        $serviceProxyBuilder = new ServiceProxyBuilder();
        $serviceProxyBuilder->setProxyFactory($this->buildProxyFactory($cacheDir));

        return $serviceProxyBuilder;
    }
}
