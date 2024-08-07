<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\EventInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\InvalidateCacheInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\SecurityInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\TransactionInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event\EventHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Security\SecurityHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Transaction\TransactionHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ClassWithCacheAttributes;
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
                new InvalidateCacheInterceptor(),
            ]
        );
    }

    public function testWithoutAnnotationReturnServiceProxyInterface(): void
    {
        $inputClass = new WithoutAnnotationClass();
        $inputClass->field = true;
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertTrue($proxy->aMethodWithoutAnnotation());
        $this->assertTrue($proxy->aMethodWithoutServiceProxyAnnotation());
        $this->assertNotProxy($inputClass, $proxy);
    }

    public function testWithCacheAnnotationReturnServiceProxyCacheInterface(): void
    {
        $inputClass = new ClassWithCacheAttributes();
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertProxy($inputClass, $proxy);
        $this->assertTrue($proxy->methodWithoutAttribute());
    }

    public function testWithCacheAnnotationWithConstructorReturnServiceProxyCacheInterface(): void
    {
        $inputClass = new WithConstructorAnnotationClass('test');
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
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
                InvalidateCacheInterceptor::class,
                CacheInterceptor::class,
                EventInterceptor::class,
            ],
            $suffixInterceptorsClasses
        );
    }
}
