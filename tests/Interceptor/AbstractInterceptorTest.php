<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Cache;
use OpenClassrooms\ServiceProxy\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Contract\Exception\DuplicatedDefaultHandler;
use OpenClassrooms\ServiceProxy\Contract\Exception\DuplicatedHandler;
use OpenClassrooms\ServiceProxy\Contract\Exception\HandlerNotFound;
use OpenClassrooms\ServiceProxy\Contract\Exception\MissingDefaultHandler;
use OpenClassrooms\ServiceProxy\Interceptor\CacheInterceptor;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache\CacheHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Cache\CacheAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class AbstractInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    /**
     * @test
     */
    public function WithNotFoundHandler_ThrowException(): void
    {
        $this->expectException(HandlerNotFound::class);
        $this->getProxyFactory(
            [new CacheInterceptor([new CacheHandlerMock()])]
        )->createProxy(new CacheAnnotatedClass())->invalidHandler();
    }

    /**
     * @test
     */
    public function WithMultipleHandlers_WithTheSameName_ThrowException(): void
    {
        $this->expectException(DuplicatedHandler::class);
        new CacheInterceptor([new CacheHandlerMock(), new CacheHandlerMock()]);
    }

    /**
     * @test
     */
    public function WithMultipleDefaultHandlers_ThrowException(): void
    {
        $this->expectException(DuplicatedDefaultHandler::class);
        new CacheInterceptor([new CacheHandlerMock(), new CacheHandlerMock('other')]);
    }

    /**
     * @test
     */
    public function WithMultipleHandlers_NoDefault_ThrowException(): void
    {
        $this->expectException(MissingDefaultHandler::class);
        new CacheInterceptor([
                                 new CacheHandlerMock('other', false),
                                 new CacheHandlerMock('other2', false),
                             ]);
    }

    /**
     * @test
     */
    public function WithMultipleHandlers_WithDifferentName_DoNotThrowException(): void
    {
        $interceptor = new CacheInterceptor([
                                                new CacheHandlerMock(),
                                                new CacheHandlerMock('other', false),
                                            ]);
        $handler = $interceptor->getHandler(
            CacheHandler::class,
            new Cache(['handler' => 'other'])
        );
        $this->assertSame('other', $handler->getName());
    }

    /**
     * @test
     */
    public function WithOneHandler_NoDefault_ReturnFirst(): void
    {
        $interceptor = new CacheInterceptor([
                                                new CacheHandlerMock('other', false),
                                            ]);
        $handler = $interceptor->getHandler(
            CacheHandler::class,
            new Cache()
        );
        $this->assertSame('other', $handler->getName());
    }

}
