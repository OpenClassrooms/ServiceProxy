<?php

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

    /**
     * @test
     */
    public function Exception_Transaction_RollBack(): void
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

    /**
     * @test
     */
    public function Transaction_Commit(): void
    {
        $this->proxy->annotatedMethod();
        $this->assertTrue($this->handler->committed);
        $this->assertFalse($this->handler->rollBacked);
    }

    /**
     * @test
     */
    public function Exception_NestedTransaction_RollBack(): void
    {
        try {
            $this->proxy->nestedAnnotatedMethodWithException();
            $this->fail();
        } catch (\Exception $e) {
            $this->assertFalse($this->handler->committed);
            $this->assertTrue($this->handler->rollBacked);
        }
    }

    /**
     * @test
     */
    public function NestedTransaction_Commit(): void
    {
        $this->proxy->nestedAnnotatedMethod();

        $this->assertTrue($this->handler->committed);
        $this->assertFalse($this->handler->rollBacked);
    }

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
}
