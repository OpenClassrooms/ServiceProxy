<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use PHPUnit_Framework_Assert as Assert;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
trait ServiceProxyTest
{
    protected static $cacheDir = __DIR__.'/cache';

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

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove(self::$cacheDir);
    }
}
