<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\InvalidateCacheInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\CacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class InvalidateCacheInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private CacheInterceptor $cacheInterceptor;

    private InvalidateCacheInterceptor $invalidateCacheInterceptor;

    private CacheHandlerMock $cacheHandlerMock;

    private ProxyFactory $proxyFactory;

    private CacheAnnotatedClass $proxy;

    protected function setUp(): void
    {
        $this->cacheHandlerMock = new CacheHandlerMock();
        $this->cacheInterceptor = new CacheInterceptor([$this->cacheHandlerMock]);
        $this->invalidateCacheInterceptor = new InvalidateCacheInterceptor([$this->cacheHandlerMock]);

        $this->proxyFactory = $this->getProxyFactory([
            $this->cacheInterceptor,
            $this->invalidateCacheInterceptor,
        ]);

        $this->proxy = $this->proxyFactory->createProxy(new CacheAnnotatedClass());
    }

    public function testInCacheCanBeInvalidated(): void
    {
        $this->proxy->methodWithTaggedCache();
        $this->assertEmpty($this->cacheInterceptor->getHits());

        $this->proxy->methodWithTaggedCache();
        $this->assertNotEmpty($this->cacheInterceptor->getHits());

        $this->proxy->methodWithInvalidateCacheAttribute();
        $this->proxy->methodWithTaggedCache();

        $this->assertEmpty($this->cacheInterceptor->getHits());
    }

    public function testCacheIsNotInvalidatedOnException(): void
    {
        $this->proxy->methodWithTaggedCache();
        $this->assertEmpty($this->cacheInterceptor->getHits());

        $this->proxy->methodWithTaggedCache();
        $this->assertNotEmpty($this->cacheInterceptor->getHits());

        try {
            $this->proxy->methodWithInvalidateCacheAndException();
        } catch (\Exception $e) {
        } finally {
            $this->proxy->methodWithTaggedCache();
            $this->assertNotEmpty($this->cacheInterceptor->getHits());
        }
    }
}
