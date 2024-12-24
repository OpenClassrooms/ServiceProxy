<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Cache;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Exception\DuplicatedHandler;
use OpenClassrooms\ServiceProxy\Handler\Exception\HandlerNotFound;
use OpenClassrooms\ServiceProxy\Handler\Exception\MissingDefaultHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Config\CacheInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\ClassWithCacheAttributes;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class AbstractInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    public function testWithNotFoundHandlerThrowException(): void
    {
        $config = new CacheInterceptorConfig();
        $this->expectException(HandlerNotFound::class);
        $this->getProxyFactory(
            [new CacheInterceptor($config, [new CacheHandlerMock()])]
        )->createInstance(ClassWithCacheAttributes::class)
            ->invalidHandler();
    }

    public function testWithMultipleHandlersWithTheSameNameThrowException(): void
    {
        $this->expectException(DuplicatedHandler::class);
        new CacheInterceptor(new CacheInterceptorConfig(), [new CacheHandlerMock(), new CacheHandlerMock()]);
    }

    public function testWithMultipleHandlersNoDefaultThrowException(): void
    {
        $this->expectException(MissingDefaultHandler::class);
        new CacheInterceptor(new CacheInterceptorConfig(), [
            new CacheHandlerMock('other', false),
            new CacheHandlerMock('other2', false),
        ]);
    }

    public function testWithMultipleHandlersWithDifferentNameDoNotThrowException(): void
    {
        $interceptor = new CacheInterceptor(new CacheInterceptorConfig(), [
            new CacheHandlerMock(),
            new CacheHandlerMock('other', false),
        ]);
        $handler = $interceptor->getHandlers(
            CacheHandler::class,
            new Cache([
                'handler' => 'other',
            ])
        )[0];
        $this->assertSame('other', $handler->getName());
    }

    public function testWithOneHandlerNoDefaultReturnFirst(): void
    {
        $interceptor = new CacheInterceptor(new CacheInterceptorConfig(), [
            new CacheHandlerMock('other', false),
        ]);
        $handler = $interceptor->getHandlers(
            CacheHandler::class,
            new Cache()
        )[0];
        $this->assertSame('other', $handler->getName());
    }
}
