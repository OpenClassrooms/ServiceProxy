<?php

namespace OpenClassrooms\ServiceProxy\Proxy\Factory;

use OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator\ServiceProxyGenerator;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use ProxyManager\Configuration;
use ProxyManager\Factory\AbstractBaseFactory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ProxyFactory extends AbstractBaseFactory implements ProxyFactoryInterface
{
    /**
     * @var ServiceProxyGenerator
     */
    private $generator;

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir = null)
    {
        if (null === $cacheDir) {
            $cacheDir = sys_get_temp_dir();
        }
        $fs = new Filesystem();
        $fs->mkdir($cacheDir);
        $configuration = new Configuration();
        $configuration->setProxiesTargetDir($cacheDir);
        parent::__construct($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function createProxy($instance)
    {
        $proxyClassName = $this->generateProxy(get_class($instance));
        /** @var ServiceProxyInterface $proxy */
        $proxy = new $proxyClassName();
        $proxy->setProxy_RealSubject($instance);

        return $proxy;
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
