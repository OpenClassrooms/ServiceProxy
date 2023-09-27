<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Handler\Exception\HandlerNotFound;
use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ClassWithCacheAttributes;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\LegacyCacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\ParameterClassStub;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class CacheInterceptorTest extends TestCase
{
    use ProxyTestTrait {
        tearDown as protected proxyTearDown;
    }

    private CacheInterceptor $cacheInterceptor;

    private CacheHandlerMock $cacheHandlerMock;

    private ProxyFactory $proxyFactory;

    private Filesystem $filesystem;

    private string $tmpDir = __DIR__ . '/tmp';

    private string $templateFilePath = __DIR__ . '/templates/CachedClass.php.template';

    protected function setUp(): void
    {
        $config = new CacheInterceptorConfig();
        $this->cacheHandlerMock = new CacheHandlerMock();
        $this->cacheInterceptor = new CacheInterceptor($config, [$this->cacheHandlerMock]);
        $this->proxyFactory = $this->getProxyFactory([
            $this->cacheInterceptor,
        ]);
        $this->filesystem = new Filesystem();
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->tmpDir);
        $this->proxyTearDown();
    }

    public function testSupportsCacheAttribute(): void
    {
        $method = Instance::createFromMethod(
            new ClassWithCacheAttributes(),
            'methodWithoutAttribute'
        );

        $this->assertFalse($this->cacheInterceptor->supportsPrefix($method));
        $this->assertFalse($this->cacheInterceptor->supportsSuffix($method));

        $method = Instance::createFromMethod(
            new ClassWithCacheAttributes(),
            'methodWithAttribute'
        );

        $this->assertTrue($this->cacheInterceptor->supportsPrefix($method));
        $this->assertTrue($this->cacheInterceptor->supportsSuffix($method));
    }

    public function testNotSupportsCacheAnnotation(): void
    {
        $method = Instance::createFromMethod(
            new LegacyCacheAnnotatedClass(),
            'annotatedMethod'
        );

        $this->assertFalse($this->cacheInterceptor->supportsPrefix($method));
        $this->assertFalse($this->cacheInterceptor->supportsSuffix($method));
    }

    public function testMethodWithoutCache(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());

        $this->assertEquals(ClassWithCacheAttributes::DATA, $proxy->methodWithAttribute());
    }

    public function testNotInCacheReturnData(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());

        $this->assertEquals(ClassWithCacheAttributes::DATA, $proxy->methodWithAttribute());
        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());
    }

    public function testInCacheReturnData(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());
        $proxy->methodWithAttribute();

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());

        $result = $proxy->methodWithAttribute();

        $this->assertEquals(ClassWithCacheAttributes::DATA, $result);
        $this->assertNotEmpty($this->cacheInterceptor->getHits());
        $this->assertEmpty($this->cacheInterceptor->getMisses());
    }

    public function testMethodWithVoidReturnIsNotCached(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());
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
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());
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

    public function testCodeUpdateInvalidatesCache(): void
    {
        $proxy = $this->writeProxy(
            className: 'ClassWithCache',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return "FOO";',
        );

        $proxy->execute();
        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());

        $proxy->execute();
        $this->assertNotEmpty($this->cacheInterceptor->getHits());
        $this->assertEmpty($this->cacheInterceptor->getMisses());

        $proxy = $this->writeProxy(
            className: 'ClassWithCache',
            methodName: 'execute',
            methodAttribute: '#[\OpenClassrooms\ServiceProxy\Attribute\Cache]',
            methodBody: 'return "BAR";',
        );

        $proxy->execute();
        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());
    }

    public function testOnExceptionDontSave(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());

        try {
            $proxy->methodWithException();
        } catch (\Exception $e) {
        }

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());

        try {
            $proxy->methodWithException();
        } catch (\Exception $e) {
        }

        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());
    }

    public function testWithLifeTimeReturnData(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());

        $data = $proxy->methodWithLifetime();

        $this->assertEquals(ClassWithCacheAttributes::DATA, $data);
        $this->assertEquals(60, CacheHandlerMock::$lifeTime);
    }

    public function testWithTagsReturnDataAndCanBeInvalidated(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());
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
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());
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

    public function testInvalidHandlerThrowsException(): void
    {
        $this->expectException(HandlerNotFound::class);
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());
        $proxy->invalidHandler();
    }

    public function testInvalidPoolThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());
        $proxy->invalidPool();
    }

    public function testPool(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());

        $this->assertEquals(ClassWithCacheAttributes::DATA, $proxy->methodWithPool());
        $this->assertEmpty($this->cacheInterceptor->getHits());
        $this->assertNotEmpty($this->cacheInterceptor->getMisses());
    }

    public function testBothHandlerAndPool(): void
    {
        $proxy = $this->proxyFactory->createProxy(new ClassWithCacheAttributes());
        $this->assertEquals(ClassWithCacheAttributes::DATA, $proxy->bothHandlerAndPool());
    }

    private function writeProxy(
        string $className,
        string $methodName,
        ?string $methodAttribute = '',
        ?string $methodArgs = '',
        ?string $methodReturnType = '',
        string $methodBody = ''
    ): object {
        $namespace = __NAMESPACE__ . str_replace('/', '\\', str_replace(__DIR__, '', $this->tmpDir));

        $content = file_get_contents($this->templateFilePath);
        $content = str_replace('__CLASS_NAME__', $className, $content);
        $content = str_replace('__METHOD_NAME__', $methodName, $content);
        $content = str_replace('__METHOD_BODY__', $methodBody, $content);
        $content = str_replace('__METHOD_ARGS__', $methodArgs, $content);
        $content = str_replace('__METHOD_ATTRIBUTE__', $methodAttribute, $content);
        $content = str_replace('__METHOD_RETURN_TYPE__', $methodReturnType ? ': ' . $methodReturnType : '', $content);
        $content = str_replace('__NAMESPACE__', $namespace, $content);

        $filePath = $this->tmpDir . '/' . $className . '.php';
        $this->filesystem->dumpFile($filePath, $content);

        $fqcn = $namespace . '\\' . $className;

        return $this->proxyFactory->createProxy(new $fqcn());
    }
}
