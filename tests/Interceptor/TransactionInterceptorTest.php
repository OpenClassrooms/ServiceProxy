<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Interceptor;

use OpenClassrooms\ServiceProxy\Interceptor\Interceptor\TransactionInterceptor;
use OpenClassrooms\ServiceProxy\ProxyFactory;
use OpenClassrooms\ServiceProxy\Tests\Double\Mock\Transaction\TransactionHandlerMock;
use OpenClassrooms\ServiceProxy\Tests\Double\Stub\Transaction\ClassWithTransactionAttribute;
use OpenClassrooms\ServiceProxy\Tests\ProxyTestTrait;
use PHPUnit\Framework\TestCase;

final class TransactionInterceptorTest extends TestCase
{
    use ProxyTestTrait {
        tearDown as protected proxyTearDown;
    }

    private TransactionHandlerMock $handler;

    private ClassWithTransactionAttribute $proxy;

    private ProxyFactory $proxyFactory;

    private TransactionInterceptor $interceptor;

    protected function setUp(): void
    {
        $this->handler = new TransactionHandlerMock();
        $this->interceptor = new TransactionInterceptor([$this->handler]);
        $this->proxyFactory = $this->getProxyFactory(
            [
                $this->interceptor,
            ]
        );
        $this->proxy = $this->proxyFactory->createProxy(new ClassWithTransactionAttribute());
    }

    public function testExceptionTransactionRollBack(): void
    {
        try {
            $this->proxy->methodWithException();
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
            $this->proxy->methodWithExceptionMappingThatThrowsException();
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
        $this->proxy->method();
        $this->assertTrue($this->handler->committed);
        $this->assertFalse($this->handler->rollBacked);
    }

    public function testExceptionNestedTransactionRollBack(): void
    {
        try {
            $this->proxy->nestedMethodThatThrowsException();
            $this->fail();
        } catch (\Exception $e) {
            $this->assertFalse($this->handler->committed);
            $this->assertTrue($this->handler->rollBacked);
        }
    }

    public function testNestedTransactionCommit(): void
    {
        $this->proxy->nestedMethod();

        $this->assertTrue($this->handler->committed);
        $this->assertFalse($this->handler->rollBacked);
    }
}
