<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\ServiceProxyBuilderInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\WithoutAnnotationClass;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyBuilderTest extends \PHPUnit_Framework_TestCase
{
    use ServiceProxyTest;

    /**
     * @var ServiceProxyBuilderInterface
     */
    private $builder;

    /**
     * @test
     */
    public function WithoutAnnotation_ReturnServiceProxyInterface()
    {
        $inputClass = new WithoutAnnotationClass();
        /** @var WithoutAnnotationClass|ServiceProxyInterface $proxy */
        $proxy = $this->builder
            ->create($inputClass)
            ->build();

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
        /** @var ServiceProxyCacheInterface|CacheAnnotationClass $proxy */
        $proxy = $this->builder
            ->create($inputClass)
            ->withCache(new CacheProviderDecorator(new ArrayCache()))
            ->build();

        $this->assertServiceCacheProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethod());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->builder = $this->buildServiceProxyBuilder();
    }
}
