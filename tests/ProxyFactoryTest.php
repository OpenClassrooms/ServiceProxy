<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\Interceptor\CacheInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\WithConstructorAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\CacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\WithoutAnnotationClass;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ProxyFactoryTest extends TestCase
{
    use ProxyTestTrait;

    private ProxyFactory $factory;

    /**
     * @test
     */
    public function WithoutAnnotation_ReturnServiceProxyInterface(): void
    {
        $inputClass = new WithoutAnnotationClass();
        $inputClass->field = true;
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertTrue($proxy->aMethodWithoutAnnotation());
        $this->assertTrue($proxy->aMethodWithoutServiceProxyAnnotation());
        $this->assertNotProxy($inputClass, $proxy);
    }

    /**
     * @test
     */
    public function WithCacheAnnotation_ReturnServiceProxyCacheInterface(): void
    {
        $inputClass = new CacheAnnotatedClass();
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertProxy($inputClass, $proxy);
        $this->assertTrue($proxy->nonAnnotatedMethod());
    }

    /**
     * @test
     */
    public function WithCacheAnnotationWithConstructor_ReturnServiceProxyCacheInterface(): void
    {
        $inputClass = new WithConstructorAnnotationClass('test');
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    protected function setUp(): void
    {
        $this->factory = $this->getProxyFactory(
            [
                new CacheInterceptor([new CacheHandlerMock()]),
            ]
        );
    }
}
