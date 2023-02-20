<?php

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

    /**
     * @test
     */
    public function TooLongId_WithId_ThrowException(): void
    {
        $this->expectException(AnnotationException::class);
        $this->proxyFactory->createProxy(new InvalidIdCacheAnnotatedClass());
    }

    /**
     * @test
     */
    public function OnException_DontSave(): void
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

    /**
     * @test
     */
    public function NotInCache_ReturnData(): void
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

    /**
     * @test
     */
    public function InCache_ReturnData(): void
    {
        $inCacheData = 'InCacheData';
        $this->cacheHandlerMock->save(
            md5(CacheAnnotatedClass::class . '::annotatedMethod'),
            $inCacheData
        );
        $data = $this->proxy->annotatedMethod();
        $this->assertEquals($inCacheData, $data);
    }

    /**
     * @test
     */
    public function WithLifeTime_ReturnData(): void
    {
        $data = $this->proxy->cacheWithLifeTime();
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(60, CacheHandlerMock::$lifeTime);
    }

    /**
     * @test
     */
    public function WithId_ReturnData(): void
    {
        $data = $this->proxy->cacheWithId();
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(CacheAnnotatedClass::DATA, $this->cacheHandlerMock->fetch('test'));
    }

    /**
     * @test
     */
    public function WithIdAndParameters_ReturnData(): void
    {
        $data = $this->proxy->cacheWithIdAndParameters(new ParameterClassStub(), 'param 2');
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(CacheAnnotatedClass::DATA, $this->cacheHandlerMock->fetch('test1'));
    }

    /**
     * @test
     */
    public function WithNamespace_ReturnData(): void
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

    /**
     * @test
     */
    public function WithNamespaceAndParameters_ReturnData(): void
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
}
