<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\TransactionInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Transaction\TransactionHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Transaction\TransactionAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class TransactionInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private TransactionHandlerMock $handler;

    private TransactionAnnotatedClass $proxy;

    private ProxyFactory $proxyFactory;

    protected function setUp(): void
    {
        $this->handler = new TransactionHandlerMock();
        $this->proxyFactory = $this->getProxyFactory(
            [
                new TransactionInterceptor(
                    [$this->handler],
                ),
            ]
        );
        $this->proxy = $this->proxyFactory->createProxy(new TransactionAnnotatedClass());
    }

    public function testExceptionTransactionRollBack(): void
    {
        try {
            $this->proxy->annotatedMethodThatThrowsException();
            /** @noinspection PhpUnreachableStatementInspection */
            $this->fail();
        } catch (\Exception $e) {
            $this->assertFalse($this->handler->committed);
            $this->assertTrue($this->handler->rollBacked);
        }
    }

    public function testExceptionTransactionRollBackWithExceptionMapping(): void
    {
        try {
            $this->proxy->annotatedMethodWithExceptionMappingThatThrowsException();
            /** @noinspection PhpUnreachableStatementInspection */
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertFalse($this->handler->committed);
            $this->assertTrue($this->handler->rollBacked);
        }
    }

    public function testTransactionCommit(): void
    {
        $this->proxy->annotatedMethod();
        $this->assertTrue($this->handler->committed);
        $this->assertFalse($this->handler->rollBacked);
    }

    public function testExceptionNestedTransactionRollBack(): void
    {
        try {
            $this->proxy->nestedAnnotatedMethodThatThrowsException();
            $this->fail();
        } catch (\Exception $e) {
            $this->assertFalse($this->handler->committed);
            $this->assertTrue($this->handler->rollBacked);
        }
    }

    public function testNestedTransactionCommit(): void
    {
        $this->proxy->nestedAnnotatedMethod();

        $this->assertTrue($this->handler->committed);
        $this->assertFalse($this->handler->rollBacked);
    }
}
