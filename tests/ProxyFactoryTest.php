<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\EventInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\SecurityInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\TransactionInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event\EventHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Security\SecurityHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Transaction\TransactionHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ClassWithCacheAttributes;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ClassWithAnnotationOnPrivateMethod;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ClassWithFinalMethod;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\FinalClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\WithConstructorAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\WithoutAnnotationClass;
use PHPUnit\Framework\TestCase;

final class ProxyFactoryTest extends TestCase
{
    use ProxyTestTrait;

    private ProxyFactory $factory;

    protected function setUp(): void
    {
        $this->factory = $this->getProxyFactory(
            [
                new CacheInterceptor(new CacheInterceptorConfig(), [new CacheHandlerMock()]),
                new EventInterceptor([new EventHandlerMock()]),
                new TransactionInterceptor([new TransactionHandlerMock()]),
                new SecurityInterceptor([new SecurityHandlerMock()]),
            ]
        );
    }

    public function testWithoutAnnotationReturnUnmodifiedObject(): void
    {
        $instance = $this->factory->createInstance(WithoutAnnotationClass::class);
        $instance->field = true;

        $this->assertTrue($instance->aMethodWithoutAnnotation());
        $this->assertTrue($instance->aMethodWithoutServiceProxyAnnotation());
        $this->assertNotProxy(WithoutAnnotationClass::class, $instance);
    }


    public function testThrownExceptionWhenCreateAProxyOnFinalClass(): void
    {
        $this->expectException(\LogicException::class);
        $this->factory->createInstance(FinalClass::class);
    }

    public function testThrownExceptionOnFinalMethodInterception(): void
    {
        $this->expectException(\LogicException::class);
        $this->factory->createInstance(ClassWithFinalMethod::class);
    }

    public function testThrownExceptionOnPrivateMethodInterception(): void
    {
        $this->expectException(\LogicException::class);
        $this->factory->createInstance(ClassWithAnnotationOnPrivateMethod::class);
    }

    public function testWithCacheAnnotationReturnServiceProxyCacheInterface(): void
    {
        $instance = $this->factory->createInstance(ClassWithCacheAttributes::class);

        $this->assertProxy(ClassWithCacheAttributes::class, $instance);
        $this->assertTrue($instance->methodWithoutAttribute());
    }

    public function testWithCacheAnnotationWithConstructorReturnServiceProxyCacheInterface(): void
    {
        $instance = $this->factory->createInstance(WithConstructorAnnotationClass::class, 'test');

        $this->assertProxy(WithConstructorAnnotationClass::class, $instance);
        $this->assertTrue($instance->aMethodWithoutAnnotation());
    }

    public function testCheckInterceptorsOrders(): void
    {
        $interceptors = $this->factory->getInterceptors();

        $prefixInterceptorsClasses = array_map(
            static fn ($interceptor) => \get_class($interceptor),
            $interceptors[PrefixInterceptor::PREFIX_TYPE]
        );
        $suffixInterceptorsClasses = array_map(
            static fn ($interceptor) => \get_class($interceptor),
            $interceptors[SuffixInterceptor::SUFFIX_TYPE]
        );

        $this->assertEquals(
            [
                SecurityInterceptor::class,
                EventInterceptor::class,
                CacheInterceptor::class,
                TransactionInterceptor::class,
            ],
            $prefixInterceptorsClasses
        );
        $this->assertEquals(
            [
                TransactionInterceptor::class,
                CacheInterceptor::class,
                EventInterceptor::class,
                SecurityInterceptor::class,
            ],
            $suffixInterceptorsClasses
        );
    }
}
