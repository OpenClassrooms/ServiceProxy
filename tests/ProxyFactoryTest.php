<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\EventInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\SecurityInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\TransactionInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Event\EventHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Security\SecurityHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Transaction\TransactionHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\CacheAnnotatedClass;
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
                new CacheInterceptor([new CacheHandlerMock()]),
                new EventInterceptor([new EventHandlerMock()]),
                new TransactionInterceptor([new TransactionHandlerMock()]),
                new SecurityInterceptor([new SecurityHandlerMock()]),
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
        $inputClass = new CacheAnnotatedClass();
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertProxy($inputClass, $proxy);
        $this->assertTrue($proxy->nonAnnotatedMethod());
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
                CacheInterceptor::class,
                EventInterceptor::class,
                SecurityInterceptor::class,
            ],
            $suffixInterceptorsClasses
        );
    }
}
