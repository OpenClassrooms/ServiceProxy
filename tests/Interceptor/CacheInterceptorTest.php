<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use Doctrine\Common\Annotations\AnnotationException;
use OpenClassrooms\ServiceProxy\Interceptor\Exception\DeprecatedAttributeException;
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
                    str_replace('\\', '.', CacheAnnotatedClass::class) . '.cacheMethodWithException'
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
                str_replace('\\', '.', CacheAnnotatedClass::class) . '.annotatedMethod'
            )
        );
    }

    public function testInCacheReturnData(): void
    {
        $inCacheData = 'InCacheData';
        $this->cacheHandlerMock->save(
            str_replace('\\', '.', CacheAnnotatedClass::class) . '.annotatedMethod',
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
            $this->cacheHandlerMock->fetch('test')
        );
    }

    public function testWithIdAndParametersReturnData(): void
    {
        $data = $this->proxy->cacheWithIdAndParameters(new ParameterClassStub(), 'param 2');
        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch('test1')
        );
    }

    public function testWithNamespaceThrowsDeprecationException(): void
    {
        $this->expectException(DeprecatedAttributeException::class);
        $this->proxy->cacheWithNamespace();
    }

    public function testWithTagsReturnDataAndCanBeInvalidated(): void
    {
        $data = $this->proxy->cacheWithIdAndTags();

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch('test_id')
        );

        $this->cacheHandlerMock->invalidateTags(['wrong_tag']);

        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch('test_id')
        );

        $this->cacheHandlerMock->invalidateTags(['custom_tag', 'another_tag']);

        $this->assertNull($this->cacheHandlerMock->fetch('test_id'));
    }

    public function testWithTagsAndParameterReturnDataAndCanBeInvalidated(): void
    {
        $data = $this->proxy->cacheWithTagsAndParameters(new ParameterClassStub(), 'param 2');
        $cacheKey = str_replace('\\', '.', CacheAnnotatedClass::class) . '.cacheWithTagsAndParameters'
                    . '.param1.' . md5(serialize(new ParameterClassStub())) . '.param2.' . md5(serialize('param 2'));

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch($cacheKey)
        );

        $this->cacheHandlerMock->invalidateTags(['custom_tag1']);

        $this->assertNull($this->cacheHandlerMock->fetch($cacheKey));
    }

    public function testWithVersionReturnData(): void
    {
        $data = $this->proxy->cacheWithVersion(new ParameterClassStub());

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                str_replace('\\', '.', CacheAnnotatedClass::class) . '.cacheWithVersion'
                . '.v2'
            )
        );
    }

    public function testWithIdAndVersionReturnData(): void
    {
        $data = $this->proxy->cacheWithIdAndVersion(new ParameterClassStub());

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch('test_id2.v2')
        );
    }
}
