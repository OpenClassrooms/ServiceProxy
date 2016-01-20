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
     * {@inheritdoc}
     */
    public function __construct(Configuration $configuration = null)
    {
        if (null !== $configuration && sys_get_temp_dir() !== $configuration->getProxiesTargetDir()) {
            $fs = new Filesystem();
            $fs->mkdir($configuration->getProxiesTargetDir());
        }
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
