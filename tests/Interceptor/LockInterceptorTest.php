<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Handler\Contract\LockHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Impl\LockInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Lock\LockHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\LoggerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\LockAnnotatedStub;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LockInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private LockHandler $handler;

    private LoggerInterface $logger;

    private LockAnnotatedStub $proxy;

    private ProxyFactory $proxyFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new LoggerMock();
        $this->handler = new LockHandlerMock();
        $this->proxyFactory = $this->getProxyFactory(
            [
                new LockInterceptor(
                    [$this->handler],
                    $this->logger,
                ),
            ]
        );
        $this->proxy = $this->proxyFactory->createProxy(new LockAnnotatedStub());
    }

    public function test(): void
    {
        $this->proxy->execute1();
        $this->assertEmpty($this->logger->getLogs());
        $this->assertFalse($this->handler->isAcquired('key1'));
        $this->assertTrue($this->handler->hasAcquired('key1'));
        $this->proxy->execute1();
        $this->assertEmpty($this->logger->getLogs());
        $this->assertFalse($this->handler->isAcquired('key1'));
        $this->assertTrue($this->handler->hasAcquired('key1'));
        $this->proxy->execute2('toto', 'titi');
        $this->assertEmpty($this->logger->getLogs());
        $this->assertFalse($this->handler->isAcquired('key2tototiti'));
        $this->assertTrue($this->handler->hasAcquired('key2tototiti'));
    }
}
