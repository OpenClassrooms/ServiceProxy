<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use Doctrine\Common\Annotations\AnnotationException;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\LegacyCacheInterceptor as CacheInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\DoctrineCacheHandlerMock as CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\InvalidIdCacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\LegacyCacheAnnotatedClass as CacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ParameterClassStub;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class LegacyCacheInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private CacheInterceptor $cacheInterceptor;

    private CacheHandlerMock $cacheHandlerMock;

    private CacheAnnotatedClass $proxy;

    private ProxyFactory $proxyFactory;

    protected function setUp(): void
    {
        $this->cacheHandlerMock = new CacheHandlerMock();
        $this->cacheInterceptor = new CacheInterceptor([$this->cacheHandlerMock]);

        $this->proxyFactory = $this->getProxyFactory([
            $this->cacheInterceptor,
        ]);
        $this->proxy = $this->proxyFactory->createInstance(CacheAnnotatedClass::class);
    }

    public function testTooLongIdWithIdThrowException(): void
    {
        $this->expectException(AnnotationException::class);
        $this->proxyFactory->createInstance(InvalidIdCacheAnnotatedClass::class);
    }

    public function testTagsInvalidationThrowException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->cacheHandlerMock->invalidateTags('default', ['foo']);
    }

    public function testOnExceptionDontSave(): void
    {
        try {
            $this->proxy->annotatedMethodWithException();
            /** @noinspection PhpUnreachableStatementInspection */
            $this->fail('Exception should be thrown');
        } catch (\Exception $e) {
            $this->assertFalse(
                $this->cacheHandlerMock->fetch(
                    'default',
                    CacheAnnotatedClass::class . '::cacheMethodWithException'
                )->isHit()
            );
        }
    }

    public function testNotInCacheReturnData(): void
    {
        $data = $this->proxy->annotatedMethod();
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                'default',
                md5(CacheAnnotatedClass::class . '::annotatedMethod')
            )->get()
        );
    }

    public function testInCacheReturnData(): void
    {
        $inCacheData = 'InCacheData';
        $this->cacheHandlerMock->save(
            'default',
            md5(CacheAnnotatedClass::class . '::annotatedMethod'),
            $inCacheData
        );
        $data = $this->proxy->annotatedMethod();
        $this->assertEquals($inCacheData, $data);
    }

    public function testInCacheWithNamespaceReturnData(): void
    {
        $inCacheData = 'InCacheData';
        $this->cacheHandlerMock->save(
            'default',
            md5(CacheAnnotatedClass::class . '::cacheWithNamespace'),
            $inCacheData
        );
        $data = $this->proxy->cacheWithNamespace();
        $this->assertEquals($inCacheData, $data);
    }

    public function testWithLifeTimeReturnData(): void
    {
        $data = $this->proxy->cacheWithLifeTime();
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(60, CacheHandlerMock::$lifeTime);
    }

    public function testWithIdReturnData(): void
    {
        $data = $this->proxy->cacheWithId();
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(CacheAnnotatedClass::DATA, $this->cacheHandlerMock->fetch('default', 'test')->get());
    }

    public function testWithIdAndParametersReturnData(): void
    {
        $data = $this->proxy->cacheWithIdAndParameters(new ParameterClassStub(), 'param 2');
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(CacheAnnotatedClass::DATA, $this->cacheHandlerMock->fetch('default', 'test1')->get());
    }

    public function testWithNamespaceReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespace();

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                'default',
                $this->cacheHandlerMock->fetch('default', md5('test-namespace'))->get() .
                md5(CacheAnnotatedClass::class . '::cacheWithNamespace')
            )->get()
        );
    }

    public function testWithNamespaceAndIdReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespaceAndId();

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                'default',
                $this->cacheHandlerMock->fetch('default', md5('test-namespace'))->get() .
                'toto'
            )->get()
        );
    }

    public function testWithNamespaceAndParametersReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespaceAndParameters(new ParameterClassStub(), 'param 2');

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                'default',
                $this->cacheHandlerMock->fetch('default', md5('test-namespace1'))->get() .
                md5(
                    CacheAnnotatedClass::class . '::cacheWithNamespaceAndParameters'
                    . '::' . serialize(new ParameterClassStub()) . '::' . serialize('param 2')
                )
            )->get()
        );
    }
}
