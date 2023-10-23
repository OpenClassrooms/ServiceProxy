<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\InvalidateCacheInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\CacheTrait;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ClassWithInvalidateCacheAttributes;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class InvalidateCacheInterceptorTest extends TestCase
{
    use ProxyTestTrait, CacheTrait {
        ProxyTestTrait::tearDown as protected proxyTearDown;
        CacheTrait::tearDown as protected cacheTearDown;
    }

    private CacheInterceptor $cacheInterceptor;

    private InvalidateCacheInterceptor $invalidateCacheInterceptor;

    private CacheHandlerMock $cacheHandlerMock;

    private ProxyFactory $proxyFactory;

    private ClassWithInvalidateCacheAttributes $proxy;

    protected function setUp(): void
    {
        $this->cacheHandlerMock = $this->getCacheHandlerMock();
        $this->cacheInterceptor = new CacheInterceptor(new CacheInterceptorConfig(), [$this->cacheHandlerMock]);
        $this->invalidateCacheInterceptor = new InvalidateCacheInterceptor([$this->cacheHandlerMock]);

        $this->proxyFactory = $this->getProxyFactory([
            $this->cacheInterceptor,
            $this->invalidateCacheInterceptor,
        ]);

        $this->proxy = $this->proxyFactory->createProxy(new ClassWithInvalidateCacheAttributes());
    }

    protected function tearDown(): void
    {
        $this->proxyTearDown();
        $this->cacheTearDown();
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

    public function testCacheInvalidationThrowExceptionIfNoTagCanBeGuessed(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->proxy->methodWithInvalidateCacheButNoTagNorResponseObject();
    }

    public function testCacheInvalidationGuessesTags(): void
    {
        $this->proxy->methodWithCacheButNoTag();
        $this->assertEmpty($this->cacheInterceptor->getHits());

        $this->proxy->methodWithCacheButNoTag();
        $this->assertNotEmpty($this->cacheInterceptor->getHits());

        $this->proxy->methodWithInvalidateCacheButNoTag();

        $this->proxy->methodWithCacheButNoTag();
        $this->assertEmpty($this->cacheInterceptor->getHits());
    }

    public function testGuessedTagsCanBeManuallyInvalidated(): void
    {
        $this->proxy->methodWithCacheButNoTag();
        $this->assertEmpty($this->cacheInterceptor->getHits());

        $this->proxy->methodWithCacheButNoTag();
        $this->assertNotEmpty($this->cacheInterceptor->getHits());

        $this->proxy->methodWithInvalidateCacheAndExplicitTag();

        $this->proxy->methodWithCacheButNoTag();
        $this->assertEmpty($this->cacheInterceptor->getHits());
    }

    public function testCacheInvalidationWithTagsFromSubResources(): void
    {
        $this->proxy->methodWithCachedEmbeddedResponse();
        $this->assertEmpty($this->cacheInterceptor->getHits());

        $this->proxy->methodWithCachedEmbeddedResponse();
        $this->assertNotEmpty($this->cacheInterceptor->getHits());

        $this->proxy->methodInvalidatingSubResource();
        $this->proxy->methodWithCachedEmbeddedResponse();
        $this->assertEmpty($this->cacheInterceptor->getHits());
    }
}
