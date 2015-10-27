<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\ServiceProxyFactoryInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\WithoutAnnotationClass;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    use ServiceProxyTest;

    /**
     * @var ServiceProxyFactoryInterface
     */
    private $factory;

    /**
     * @test
     */
    public function WithoutAnnotation_ReturnServiceProxyInterface()
    {
        $inputClass = new WithoutAnnotationClass();
        /** @var WithoutAnnotationClass|ServiceProxyInterface $proxy */
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
        $this->assertTrue($proxy->aMethodWithoutServiceProxyAnnotation());

        $this->assertNotInstanceOf('OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface', $proxy);
    }

    /**
     * @test
     */
    public function WithCacheAnnotation_ReturnServiceProxyCacheInterface()
    {
        $inputClass = new CacheAnnotationClass();
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
        $this->factory = $this->buildServiceProxyFactory();
    }
}
