<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Factory;

use OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator\ServiceProxyGenerator;
use ProxyManager\Configuration;
use ProxyManager\Factory\AbstractBaseFactory;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ProxyFactory extends AbstractBaseFactory
{
    /**
     * @var ServiceProxyGenerator
     */
    private $generator;

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $configuration = new Configuration();
        $configuration->setProxiesTargetDir($cacheDir);
        parent::__construct($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function createProxy($instanceOrClassName)
    {
        $className = is_object($instanceOrClassName) ? get_class($instanceOrClassName) : $instanceOrClassName;
        $proxyClassName = $this->generateProxy($className);

        return new $proxyClassName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getGenerator()
    {
        return $this->generator;
    }

    public function setGenerator(ServiceProxyGenerator $generator)
    {
        $this->generator = $generator;
    }
}
