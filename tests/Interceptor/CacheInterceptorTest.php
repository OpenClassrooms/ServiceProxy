<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use Doctrine\Common\Annotations\AnnotationException;
use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\CacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\InvalidIdCacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ParameterClassStub;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class CacheInterceptorTest extends TestCase
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
                    md5(CacheAnnotatedClass::class . '::cacheMethodWithException')
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
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                md5(CacheAnnotatedClass::class . '::cacheWithId') .
                'test'
            )
        );
    }

    public function testWithIdAndParametersReturnData(): void
    {
        $data = $this->proxy->cacheWithIdAndParameters(new ParameterClassStub(), 'param 2');
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                md5(CacheAnnotatedClass::class . '::cacheWithIdAndParameters'
                    . '::' . serialize(new ParameterClassStub()) . '::' . serialize('param 2')) .
                'test1'
            )
        );
    }

    public function testWithNamespaceReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespace();

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch('test-namespace')
        );
    }

    public function testWithNamespaceAndParametersReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespaceAndParameters(new ParameterClassStub(), 'param 2');

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch('test-namespace1')
        );
    }

    public function testWithNamespaceAndIdReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespaceAndId();

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch('test_namespacetest_id')
        );
    }

    public function testWithNamespaceIdAndParametersReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespaceIdAndParameters(new ParameterClassStub(), 'foo');

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch('test_namespace2test_idfoo')
        );
    }

    public function testWithTagsReturnDataAndCanBeInvalidated(): void
    {
        $data = $this->proxy->cacheWithIdAndTags();
        $cacheKey = md5(CacheAnnotatedClass::class . '::cacheWithIdAndTags') . 'test_id';

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch($cacheKey)
        );

        $this->cacheHandlerMock->invalidateTags(['wrong_tag']);

        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch($cacheKey)
        );

        $this->cacheHandlerMock->invalidateTags(['custom_tag', 'another_tag']);

        $this->assertNull($this->cacheHandlerMock->fetch($cacheKey));
    }

    public function testWithTagsAndParameterReturnDataAndCanBeInvalidated(): void
    {
        $data = $this->proxy->cacheWithTagsAndParameters(new ParameterClassStub(), 'param 2');
        $cacheKey = md5(CacheAnnotatedClass::class . '::cacheWithTagsAndParameters'
                    . '::' . serialize(new ParameterClassStub()) . '::' . serialize('param 2'));

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch($cacheKey)
        );

        $this->cacheHandlerMock->invalidateTags(['custom_tag1']);

        $this->assertNull($this->cacheHandlerMock->fetch($cacheKey));
    }
}
