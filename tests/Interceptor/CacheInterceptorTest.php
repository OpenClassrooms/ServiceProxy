<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use Doctrine\Common\Annotations\AnnotationException;
use OpenClassrooms\ServiceProxy\Interceptor\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\CacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\InvalidIdCacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ParameterClassStub;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class CacheInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private CacheInterceptor $cacheInterceptor;

    private CacheHandlerMock $cacheHandlerMock;

    private CacheAnnotatedClass $proxy;

    protected function setUp(): void
    {
        $this->cacheHandlerMock = new CacheHandlerMock();
        $this->proxyFactory = $this->getProxyFactory(
            [
                new CacheInterceptor([$this->cacheHandlerMock]),
            ]
        );
        $this->proxy = $this->proxyFactory->createProxy(new CacheAnnotatedClass());
    }

    public function testTooLongIdWithIdThrowException(): void
    {
        $this->expectException(AnnotationException::class);
        $this->proxyFactory->createProxy(new InvalidIdCacheAnnotatedClass());
    }

    public function testOnExceptionDontSave(): void
    {
        try {
            $this->proxy->annotatedMethodWithException();
            /** @noinspection PhpUnreachableStatementInspection */
            $this->fail('Exception should be thrown');
        } catch (\Exception $e) {
            $this->assertFalse(
                $this->cacheHandlerMock->contains(
                    CacheAnnotatedClass::class . '::cacheMethodWithException'
                )
            );
        }
    }

    public function testNotInCacheReturnData(): void
    {
        $data = $this->proxyCall([new CacheAnnotatedClass(), 'annotatedMethod']);
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                md5(CacheAnnotatedClass::class . '::annotatedMethod')
            )
        );
    }

    public function testInCacheReturnData(): void
    {
        $inCacheData = 'InCacheData';
        $this->cacheHandlerMock->save(
            md5(CacheAnnotatedClass::class . '::annotatedMethod'),
            $inCacheData
        );
        $data = $this->proxy->annotatedMethod();
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
        $this->assertEquals(CacheAnnotatedClass::DATA, $this->cacheHandlerMock->fetch('test'));
    }

    public function testWithIdAndParametersReturnData(): void
    {
        $data = $this->proxy->cacheWithIdAndParameters(new ParameterClassStub(), 'param 2');
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(CacheAnnotatedClass::DATA, $this->cacheHandlerMock->fetch('test1'));
    }

    public function testWithNamespaceReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespace();

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                $this->cacheHandlerMock->fetch(md5('test-namespace')) .
                md5(CacheAnnotatedClass::class . '::cacheWithNamespace')
            )
        );
    }

    public function testWithNamespaceAndParametersReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespaceAndParameters(new ParameterClassStub(), 'param 2');

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                $this->cacheHandlerMock->fetch(md5('test-namespace1')) .
                md5(
                    CacheAnnotatedClass::class . '::cacheWithNamespaceAndParameters'
                    . '::' . serialize(new ParameterClassStub()) . '::' . serialize('param 2')
                )
            )
        );
    }
}
