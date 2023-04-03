<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use Doctrine\Common\Annotations\AnnotationException;
use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\LegacyCacheInterceptor as CacheInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
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

    protected function setUp(): void
    {
        $this->cacheHandlerMock = new CacheHandlerMock('legacy_handler_name');
        $this->cacheInterceptor = new CacheInterceptor([$this->cacheHandlerMock]);

        $this->proxyFactory = $this->getProxyFactory([
            $this->cacheInterceptor,
        ]);
        $this->proxy = $this->proxyFactory->createProxy(new CacheAnnotatedClass());
    }

    public function testTooLongIdWithIdThrowException(): void
    {
        $this->expectException(AnnotationException::class);
        $this->proxyFactory->createProxy(new InvalidIdCacheAnnotatedClass());
    }

    public function testTagsInvalidationThrowException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->cacheHandlerMock->invalidateTags(['foo']);
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

    public function testInCacheWithNamespaceReturnData(): void
    {
        $inCacheData = 'InCacheData';
        $this->cacheHandlerMock->save(
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

    public function testWithNamespaceAndIdReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespaceAndId();

        $this->assertEquals(CacheAnnotatedClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotatedClass::DATA,
            $this->cacheHandlerMock->fetch(
                $this->cacheHandlerMock->fetch(md5('test-namespace')) .
                'toto'
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

    public function methodNamesProvider(): array
    {
        return [
            'legacy handler' => ['annotatedMethod', true],
            'another legacy handler' => ['annotatedMethodWithException', true],
            'legacy random handler' => ['annotatedMethodWithAnotherLegacyHandler', true],
            'invalid handler' => ['invalidHandler', false],
            'non legacy handler' => ['annotatedMethodWithNonLegacyHandler', false],
        ];
    }

    /**
     * @dataProvider methodNamesProvider
     */
    public function testSupportsLegacyHandlerAttribute(string $methodName, bool $supports): void
    {
        $method = Instance::createFromMethod(
            new CacheAnnotatedClass(),
            $methodName
        );

        $this->assertEquals($supports, $this->cacheInterceptor->supportsPrefix($method));
        $this->assertEquals($supports, $this->cacheInterceptor->supportsSuffix($method));
    }
}
