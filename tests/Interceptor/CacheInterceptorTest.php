<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Handler\Exception\HandlerNotFound;
use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\CacheTestTrait;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ClassWithCacheAttributes;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\LegacyCacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ResponseStub;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ParameterClassStub;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class CacheInterceptorTest extends TestCase
{
    use CacheTestTrait {
        CacheTestTrait::tearDown as protected cacheTearDown;
    }

    use ProxyTestTrait {
        ProxyTestTrait::tearDown as protected proxyTearDown;
    }

    private CacheInterceptor $cacheInterceptor;

    private CacheHandlerMock $cacheHandlerMock;

    private ProxyFactory $proxyFactory;

    protected function setUp(): void
    {
        $config = new CacheInterceptorConfig();
        $this->cacheHandlerMock = $this->getCacheHandlerMock();
        $this->cacheInterceptor = new CacheInterceptor($config, [$this->cacheHandlerMock]);
        $this->proxyFactory = $this->getProxyFactory([
            $this->cacheInterceptor,
        ]);
    }

    protected function tearDown(): void
    {
        $this->proxyTearDown();
        $this->cacheTearDown();
    }

    public function testSupportsCacheAttribute(): void
    {
        $method = Instance::createFromMethod(
            ClassWithCacheAttributes::class,
            'methodWithoutAttribute'
        );

        $this->assertFalse($this->cacheInterceptor->supportsPrefix($method));
        $this->assertFalse($this->cacheInterceptor->supportsSuffix($method));

        $method = Instance::createFromMethod(
            ClassWithCacheAttributes::class,
            'methodWithAttribute'
        );

        $this->assertTrue($this->cacheInterceptor->supportsPrefix($method));
        $this->assertTrue($this->cacheInterceptor->supportsSuffix($method));
    }

    public function testNotSupportsCacheAnnotation(): void
    {
        $method = Instance::createFromMethod(
            LegacyCacheAnnotatedClass::class,
            'annotatedMethod'
        );

        $this->assertFalse($this->cacheInterceptor->supportsPrefix($method));
        $this->assertFalse($this->cacheInterceptor->supportsSuffix($method));
    }

    public function testMethodWithoutCache(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);

        $this->assertEquals(ClassWithCacheAttributes::DATA, $proxy->methodWithAttribute());
    }

    public function testNotInCacheReturnData(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);

        $this->assertEquals(ClassWithCacheAttributes::DATA, $proxy->methodWithAttribute());
        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());
    }

    public function testInCacheReturnData(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->methodWithAttribute();

        $this->assertEmpty($this->cacheInterceptor::getHits());
        $this->assertNotEmpty($this->cacheInterceptor::getMisses());

        $result = $proxy->methodWithAttribute();

        $this->assertEquals(ClassWithCacheAttributes::DATA, $result);
        $this->assertNotEmpty($this->cacheInterceptor::getHits());
        $this->assertEmpty($this->cacheInterceptor::getMisses());
    }

    public function testMethodWithVoidReturnIsNotCached(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->methodWithVoidReturn();

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());

        $result = $proxy->methodWithVoidReturn();

        $this->assertNull($result);
        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());
    }

    public function testCachedMethodWithArguments(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->methodWithArguments('value1', 'value2');

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());

        $proxy->methodWithArguments('value3', 'value4');

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());

        $proxy->methodWithArguments('value1', 'value2');

        $this->assertNotEmpty($this->cacheInterceptor->getHits());
        $this->assertEmpty($this->cacheInterceptor->getMisses());

        $proxy->methodWithArguments('value3', 'value4');

        $this->assertNotEmpty($this->cacheInterceptor->getHits());
        $this->assertEmpty($this->cacheInterceptor->getMisses());
    }

    public function testOnExceptionDontSave(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);

        try {
            $proxy->methodWithException();
        } catch (\Exception) {
        }

        $this->assertEmpty($this->cacheInterceptor::getHits());
        $this->assertNotEmpty($this->cacheInterceptor::getMisses());

        try {
            $proxy->methodWithException();
        } catch (\Exception $e) {
        }

        $this->assertEmpty($this->cacheInterceptor::getHits());
        $this->assertNotEmpty($this->cacheInterceptor::getMisses());
    }

    public function testWithLifeTimeReturnData(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);

        $data = $proxy->methodWithLifetime();

        $this->assertEquals(ClassWithCacheAttributes::DATA, $data);
        $this->assertEquals(60, CacheHandlerMock::$lifeTime);
    }

    public function testWithTagsReturnDataAndCanBeInvalidated(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->methodWithTaggedCache();

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());

        $this->cacheHandlerMock->invalidateTags('default', ['wrong_tag']);

        $proxy->methodWithTaggedCache();

        $this->assertNotEmpty($this->cacheInterceptor->getHits());
        $this->assertEmpty($this->cacheInterceptor->getMisses());

        $this->cacheHandlerMock->invalidateTags('default', ['my_tag', 'another_tag']);

        $proxy->methodWithTaggedCache();

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());
    }

    public function testWithTagsAndParameterReturnDataAndCanBeInvalidated(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->methodWithResolvedTag(new ParameterClassStub());

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());

        $proxy->methodWithResolvedTag(new ParameterClassStub());

        $this->assertNotEmpty($this->cacheInterceptor->getHits());
        $this->assertEmpty($this->cacheInterceptor->getMisses());

        $this->cacheHandlerMock->invalidateTags('default', ['my_tag1']);

        $proxy->methodWithResolvedTag(new ParameterClassStub());

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());
    }

    public function testMethodCacheIsAutoTaggedFromResponse(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->methodWithAttributeReturningObject();

        $this->assertEmpty($this->cacheInterceptor::getHits());
        $this->assertNotEmpty($this->cacheInterceptor::getMisses());

        $result = $proxy->methodWithAttributeReturningObject();

        $this->assertInstanceOf(ResponseStub::class, $result);
        $this->assertNotEmpty($this->cacheInterceptor::getHits());
        $this->assertEmpty($this->cacheInterceptor::getMisses());

        $tagToInvalidate = str_replace('\\', '.', ResponseStub::class) . '.' . ResponseStub::ID;

        $this->cacheHandlerMock->invalidateTags('default', [$tagToInvalidate]);

        $result = $proxy->methodWithAttributeReturningObject();

        $this->assertEmpty($this->cacheInterceptor::getHits());
        $this->assertNotEmpty($this->cacheInterceptor::getMisses());
    }

    public function testMethodWithPhpDoc(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->methodWithAttributeAndPhpDoc();

        $this->assertEmpty($this->cacheInterceptor::getHits());
        $this->assertNotEmpty($this->cacheInterceptor::getMisses());

        $proxy->methodWithAttributeAndPhpDoc();

        $this->assertNotEmpty($this->cacheInterceptor::getHits());
        $this->assertEmpty($this->cacheInterceptor::getMisses());
    }

    public function testUnknownHandlerThrowsException(): void
    {
        $this->expectException(HandlerNotFound::class);
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->invalidHandler();
    }

    public function testUnknownPoolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->invalidPool();
    }

    public function testPool(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);

        $this->assertEquals(ClassWithCacheAttributes::DATA, $proxy->methodWithPool());
        $this->assertEmpty($this->cacheInterceptor::getHits());
        $this->assertNotEmpty($this->cacheInterceptor::getMisses());
    }

    public function testProperties(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class, 'world');

        $this->assertEquals('world', $proxy->data);
    }

    public function testBothHandlerAndPool(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $this->assertEquals(ClassWithCacheAttributes::DATA, $proxy->bothHandlerAndPool());
    }

    public function testMethodWithMultiplePools(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->methodWithMultiplePools();

        $this->assertEmpty($this->cacheInterceptor->getHits('foo'));
        $this->assertNotEmpty($this->cacheInterceptor->getMisses('foo'));

        $this->assertEmpty($this->cacheInterceptor->getHits('bar'));
        $this->assertNotEmpty($this->cacheInterceptor->getMisses('bar'));

        $result = $proxy->methodWithMultiplePools();

        $this->assertNotEmpty($this->cacheInterceptor->getHits('foo'));
        $this->assertEmpty($this->cacheInterceptor->getMisses('foo'));

        $this->assertEmpty($this->cacheInterceptor->getHits('bar'));
        $this->assertEmpty($this->cacheInterceptor->getMisses('bar'));

        $this->assertEquals(ClassWithCacheAttributes::DATA, $result);
    }

    public function testMethodWithNoPoolUsesDefaultPool(): void
    {
        $proxy = $this->proxyFactory->createInstance(ClassWithCacheAttributes::class);
        $proxy->methodWithAttribute();

        $this->assertNotEmpty($this->cacheInterceptor->getMisses('default'));
    }

    public function testCodeUpdateInvalidatesCache(): void
    {
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return "FOO";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return "BAR";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');
    }

    public function testTTLUpdateInvalidatesCache(): void
    {
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return "FOO";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache(ttl: 12)]',
            methodBody: 'return "FOO";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');
    }

    public function testReturnTypeUpdateInvalidatesCache(): void
    {
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \Symfony\Component\HttpFoundation\Response;',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        // test cache invalidation with added return type (unique class type)
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \Symfony\Component\HttpFoundation\Response;',
            methodReturnType: '\Symfony\Component\HttpFoundation\Response',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        // test cache invalidation with added return type (union with classes)
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \Symfony\Component\HttpFoundation\Response;',
            methodReturnType: '\Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\Request',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        // test cache invalidation with added return type (union with array)
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \Symfony\Component\HttpFoundation\Response;',
            methodReturnType: '\Symfony\Component\HttpFoundation\Response|array',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        // test cache invalidation with added return type (union with internal class)
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \Symfony\Component\HttpFoundation\Response;',
            methodReturnType: '\Symfony\Component\HttpFoundation\Response|\stdClass',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');
    }

    public function testUnknownReturnTypeDoesInvalidateCache(): void
    {
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \Symfony\Component\HttpFoundation\Response;',
            methodReturnType: '\Symfony\Component\HttpFoundation\Response',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \Symfony\Component\HttpFoundation\Response;',
            methodReturnType: '\Symfony\Component\HttpFoundation\Response|UnknownClass',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
    }

    public function testPhpDocReturnTypeInvalidatesCache(): void
    {
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return "FOO";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        // test cache invalidation with added phpdoc (unique return type)
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodPhpDoc: ['@return \Symfony\Component\HttpFoundation\Response'],
            methodBody: 'return "FOO";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        // test cache invalidation with added phpdoc (union with classes)
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodPhpDoc: [
                '@return \Symfony\Component\HttpFoundation\Response|\OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ResponseStub',
            ],
            methodBody: 'return "FOO";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        // test cache invalidation with added phpdoc (union return with array)
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodPhpDoc: ['@return \Symfony\Component\HttpFoundation\Response|\stdClass[]'],
            methodBody: 'return "FOO";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        // test cache invalidation with added phpdoc (with generic)
        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodPhpDoc: [
                '@return \OpenClassrooms\ServiceProxy\Tests\Double\Stub\GenericCollection<\OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ResponseStub>',
            ],
            methodBody: 'return "FOO";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');

        $this->writeClass(
            className: 'WrittenClass',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodPhpDoc: [
                '@return \OpenClassrooms\ServiceProxy\Tests\Double\Stub\GenericCollection<\Symfony\Component\HttpFoundation\Response>',
            ],
            methodBody: 'return "FOO";',
        );

        $this->executeAndAssertCacheMiss('WrittenClass');
        $this->executeAndAssertCacheHit('WrittenClass');
    }

    public function testResponseCodeUpdateInvalidatesCache(): void
    {
        $this->writeClass(
            className: 'CachedResponse',
            methodName: 'getName',
            methodBody: 'return "FOO";',
        );

        $this->writeClass(
            className: 'ClassWithCache',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \OpenClassrooms\ServiceProxy\Tests\tmp\CachedResponse;',
            methodReturnType: '\OpenClassrooms\ServiceProxy\Tests\tmp\CachedResponse',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');

        $this->writeClass(
            className: 'CachedResponse',
            methodName: 'getName',
            methodBody: 'return "BAR";',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');
    }

    public function testResponsePropertiesUpdateInvalidatesCache(): void
    {
        $this->writeClass(
            className: 'Response',
            methodName: 'getName',
            methodBody: 'return "FOO";',
            classProperties: [
                'public \OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ResponseStub $property',
            ],
        );

        $this->writeClass(
            className: 'ClassWithCache',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \OpenClassrooms\ServiceProxy\Tests\tmp\Response;',
            methodReturnType: '\OpenClassrooms\ServiceProxy\Tests\tmp\Response',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');

        // change response property type should invalidate the cache

        $this->writeClass(
            className: 'ResponseSubObject',
            methodName: 'getName',
            methodBody: 'return "FOO";',
        );

        $this->writeClass(
            className: 'Response',
            methodName: 'getName',
            methodBody: 'return "FOO";',
            classProperties: [
                'public \OpenClassrooms\ServiceProxy\Tests\tmp\ResponseSubObject $property',
            ],
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');

        // update class response sub object code should invalidate the cache

        $this->writeClass(
            className: 'ResponseSubObject',
            methodName: 'getName',
            methodBody: 'return "BAR";',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');
    }

    public function testResponseInnerPropertyPhpDocUpdateInvalidatesCache(): void
    {
        $this->writeClass(
            className: 'CachedResponse',
            methodName: 'getName',
            methodBody: 'return "FOO";',
            classProperties: [
                '/** @var \OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ResponseStub **/
                public $property',
            ],
        );

        $this->writeClass(
            className: 'ClassWithCache',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \OpenClassrooms\ServiceProxy\Tests\tmp\CachedResponse;',
            methodReturnType: '\OpenClassrooms\ServiceProxy\Tests\tmp\CachedResponse',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');

        // change response property type should invalidate the cache

        $this->writeClass(
            className: 'ResponseSubObject',
            methodName: 'getName',
            methodBody: 'return "FOO";',
        );

        $this->writeClass(
            className: 'CachedResponse',
            methodName: 'getName',
            methodBody: 'return "FOO";',
            classProperties: [
                '/** @var \OpenClassrooms\ServiceProxy\Tests\tmp\ResponseSubObject **/
                public string $name',
            ],
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');

        // change response code should invalidate the cache

        $this->writeClass(
            className: 'ResponseSubObject',
            methodName: 'getName',
            methodBody: 'return "BAR";',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');
    }

    public function testResponseInnerMethodReturnTypeUpdateInvalidatesCache(): void
    {
        $this->writeClass(
            className: 'CachedResponse',
            methodName: 'getStub',
            methodBody: 'return new \OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ResponseStub();',
            methodReturnType: '\OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ResponseStub',
        );

        $this->writeClass(
            className: 'ClassWithCache',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \OpenClassrooms\ServiceProxy\Tests\tmp\CachedResponse;',
            methodReturnType: '\OpenClassrooms\ServiceProxy\Tests\tmp\CachedResponse',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');

        // change response return type type should invalidate the cache

        $this->writeClass(
            className: 'ResponseSubObject',
            methodName: 'getName',
            methodBody: 'return "FOO";',
        );

        $this->writeClass(
            className: 'CachedResponse',
            methodName: 'getName',
            methodBody: 'return new \OpenClassrooms\ServiceProxy\Tests\tmp\ResponseSubObject();',
            methodReturnType: '\OpenClassrooms\ServiceProxy\Tests\tmp\ResponseSubObject',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');

        // change response code should invalidate the cache

        $this->writeClass(
            className: 'ResponseSubObject',
            methodName: 'getName',
            methodBody: 'return "BAR";',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');
    }

    public function testResponseInnerMethodPhpDocUpdateInvalidatesCache(): void
    {
        $this->writeClass(
            className: 'CachedResponse',
            methodName: 'getStub',
            methodBody: 'return new \OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ResponseStub();',
            methodPhpDoc: ['@return \OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ResponseStub'],
        );

        $this->writeClass(
            className: 'ClassWithCache',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return new \OpenClassrooms\ServiceProxy\Tests\tmp\CachedResponse;',
            methodReturnType: '\OpenClassrooms\ServiceProxy\Tests\tmp\CachedResponse',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');

        // change response return type type should invalidate the cache

        $this->writeClass(
            className: 'ResponseSubObject',
            methodName: 'getName',
            methodBody: 'return "FOO";',
        );

        $this->writeClass(
            className: 'CachedResponse',
            methodName: 'getName',
            methodBody: 'return new \OpenClassrooms\ServiceProxy\Tests\tmp\ResponseSubObject();',
            methodPhpDoc: ['@return \OpenClassrooms\ServiceProxy\Tests\tmp\ResponseSubObject'],
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');

        // change response code should invalidate the cache

        $this->writeClass(
            className: 'ResponseSubObject',
            methodName: 'getName',
            methodBody: 'return "BAR";',
        );

        $this->executeAndAssertCacheMiss('ClassWithCache');
        $this->executeAndAssertCacheHit('ClassWithCache');
    }
}
