<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\Factory;

use OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator\ServiceProxyGenerator;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use ProxyManager\Configuration;
use ProxyManager\Factory\AbstractBaseFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;

class ProxyFactory extends AbstractBaseFactory implements ProxyFactoryInterface
{
    private ServiceProxyGenerator $generator;

    public function __construct(Configuration $configuration = null)
    {
        if (null !== $configuration && sys_get_temp_dir() !== $configuration->getProxiesTargetDir()) {
            $fs = new Filesystem();
            $fs->mkdir($configuration->getProxiesTargetDir());
        }
        parent::__construct($configuration);
    }

    public function createProxy($instance): ServiceProxyInterface
    {
        $proxyClassName = $this->generateProxy(get_class($instance));
        $proxy = new $proxyClassName();
        $proxy->setProxy_RealSubject($instance);

        return $proxy;
    }

    protected function getGenerator() : ProxyGeneratorInterface
    {
        return $this->generator;
    }

    public function setGenerator(ServiceProxyGenerator $generator): void
    {
        $this->generator = $generator;
    }
}
