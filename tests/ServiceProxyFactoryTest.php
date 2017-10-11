<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;
use OpenClassrooms\ServiceProxy\ServiceProxyFactory;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationWithConstructorClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\WithoutAnnotationClass;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    use ServiceProxyHelper;

    use ServiceProxyTest;

    /**
     * @var ServiceProxyFactory
     */
    private $factory;

    /**
     * @test
     */
    public function WithoutAnnotation_ReturnServiceProxyInterface()
    {
        $inputClass = new WithoutAnnotationClass();
        $inputClass->field = true;
        /** @var WithoutAnnotationClass|ServiceProxyInterface $proxy */
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
        $this->assertTrue($proxy->aMethodWithoutServiceProxyAnnotation());

        $this->assertNotInstanceOf('OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface', $proxy);
    }

    /**
     * @test
     * @expectedException \OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException
     */
    public function WithCacheAnnotationWithoutCacheProvider_ThrowException()
    {
        $inputClass = new CacheAnnotationClass();
        $this->factory->createProxy($inputClass);
    }

    /**
     * @test
     */
    public function WithCacheAnnotation_ReturnServiceProxyCacheInterface()
    {
        $inputClass = new CacheAnnotationClass();

        $this->factory->setCacheProvider(new CacheProviderDecorator(new ArrayCache()));
        /** @var ServiceProxyInterface|CacheAnnotationClass $proxy */
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertServiceCacheProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    /**
     * @test
     */
    public function WithCacheAnnotationWithConstructor_ReturnServiceProxyCacheInterface()
    {
        $inputClass = new CacheAnnotationWithConstructorClass('test');

        $this->factory->setCacheProvider(new CacheProviderDecorator(new ArrayCache()));
        /** @var ServiceProxyInterface|CacheAnnotationClass $proxy */
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertServiceCacheProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->factory = $this->getServiceProxyFactory(self::$cacheDir);
    }
}
