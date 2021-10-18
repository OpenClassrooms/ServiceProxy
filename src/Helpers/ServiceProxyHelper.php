<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Helpers;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactory;
use OpenClassrooms\ServiceProxy\Proxy\Factory\ProxyFactoryInterface;
use OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator\ServiceProxyGenerator;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestBuilder;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Response\ServiceProxyStrategyResponseBuilder;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\ServiceProxyCacheStrategy;
use OpenClassrooms\ServiceProxy\ServiceProxyBuilder;
use OpenClassrooms\ServiceProxy\ServiceProxyBuilderInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyFactory;
use ProxyManager\Configuration;

trait ServiceProxyHelper
{
    protected function getServiceProxyFactory(string $cacheDir): ServiceProxyFactory
    {
        $serviceProxyFactory = new ServiceProxyFactory();
        $configuration = new Configuration();
        $configuration->setProxiesTargetDir($cacheDir);
        $serviceProxyFactory->setProxyFactory($this->buildProxyFactory($configuration));

        return $serviceProxyFactory;
    }

    protected function buildProxyFactory(Configuration $configuration = null): ProxyFactoryInterface
    {
        $proxyFactory = new ProxyFactory($configuration);
        $proxyFactory->setGenerator($this->buildGenerator());

        return $proxyFactory;
    }

    private function buildGenerator(): ServiceProxyGenerator
    {
        $generator = new ServiceProxyGenerator();
        $generator->setAnnotationReader(new AnnotationReader());
        $generator->setCacheStrategy($this->buildCacheStrategy());
        $generator->setServiceProxyStrategyRequestBuilder(new ServiceProxyStrategyRequestBuilder());

        return $generator;
    }

    private function buildCacheStrategy(): ServiceProxyCacheStrategy
    {
        $cacheStrategy = new ServiceProxyCacheStrategy();
        $cacheStrategy->setServiceProxyStrategyResponseBuilder(new ServiceProxyStrategyResponseBuilder());

        return $cacheStrategy;
    }


    protected function getServiceProxyBuilder(string $cacheDir): ServiceProxyBuilderInterface
    {
        $serviceProxyBuilder = new ServiceProxyBuilder();
        $configuration = new Configuration();
        $configuration->setProxiesTargetDir($cacheDir);
        $serviceProxyBuilder->setProxyFactory($this->buildProxyFactory($configuration));

        return $serviceProxyBuilder;
    }
}
