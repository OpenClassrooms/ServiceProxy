<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Interceptor\TransactionInterceptor;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Transaction\TransactionHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Transaction\TransactionAnnotatedClass;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

class TransactionInterceptorTest extends TestCase
{
    use ProxyTestTrait;

    private TransactionHandlerMock $handler;

    private TransactionAnnotatedClass $proxy;

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
            $this->proxy->annotatedMethodWithException();
            /** @noinspection PhpUnreachableStatementInspection */
            $this->fail();
        } catch (\Exception $e) {
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
            $this->proxy->nestedAnnotatedMethodWithException();
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
