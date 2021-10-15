<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\Exceptions\TransactionConflictException;
use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;
use OpenClassrooms\ServiceProxy\ServiceProxyBuilder;
use OpenClassrooms\ServiceProxy\Tests\Doubles\FunctionalConflictException;
use OpenClassrooms\ServiceProxy\Tests\Doubles\PDOMock;
use OpenClassrooms\ServiceProxy\Tests\Doubles\TransactionAnnotationClass;
use OpenClassrooms\ServiceProxy\Transaction\PDOTransactionAdapter;
use PHPUnit\Framework\TestCase;

class ServiceProxyTransactionTest extends TestCase
{
    use ServiceProxyHelper;

    use ServiceProxyTest;

    private TransactionAnnotationClass $proxy;

    private ServiceProxyBuilder $serviceProxyBuilder;

    /**
     * @test
     */
    public function WithoutAnnotation_DoesNothingWithTransaction(): void
    {
        $result = $this->proxy->aMethodWithoutAnnotation();

        $this->assertTrue($result);
        $this->assertFalse(PDOMock::$transactionBegan);
        $this->assertFalse(PDOMock::$inTransaction);
        $this->assertFalse(PDOMock::$committed);
        $this->assertFalse(PDOMock::$rolledBack);
    }

    /**
     * @test
     */
    public function WithAlreadyOpenedTransaction_CommitsWithoutReopeningTransaction(): void
    {
        PDOMock::$inTransaction = true;
        $result = $this->proxy->onlyTransaction();

        $this->assertEquals(TransactionAnnotationClass::DATA, $result);
        $this->assertFalse(PDOMock::$transactionBegan);
        $this->assertFalse(PDOMock::$rolledBack);
        $this->assertTrue(PDOMock::$committed);
    }

    /**
     * @test
     */
    public function WithTransaction_CommitsAfterHavingOpenedTransaction(): void
    {
        $result = $this->proxy->onlyTransaction();

        $this->assertEquals(TransactionAnnotationClass::DATA, $result);
        $this->assertFalse(PDOMock::$rolledBack);
        $this->assertTrue(PDOMock::$transactionBegan);
        $this->assertTrue(PDOMock::$committed);
    }

    /**
     * @test
     */
    public function WithException_RollsBackAndThrowsException(): void
    {
        $this->expectException(\Exception::class);

        $this->proxy->transactionMethodWithExceptionWithoutConflictException();

        $this->assertFalse(PDOMock::$committed);
        $this->assertTrue(PDOMock::$rolledBack);
        $this->assertTrue(PDOMock::$transactionBegan);
    }

    /**
     * @test
     */
    public function WithExceptionAndConflictParam_RollsBackAndThrowsOriginalException(): void
    {
        $this->expectException(\Exception::class);

        $this->proxy->transactionMethodWithExceptionAndConflictException();

        $this->assertFalse(PDOMock::$committed);
        $this->assertTrue(PDOMock::$rolledBack);
        $this->assertTrue(PDOMock::$transactionBegan);
    }

    /**
     * @test
     */
    public function WithNonConflictPDOExceptionDuringTransaction_RollsBackAndThrowsException(): void
    {
        $this->expectException(\PDOException::class);

        PDOMock::$exception = new \PDOException('', 0);
        $this->proxy->onlyTransaction();
        $this->assertFalse(PDOMock::$committed);
        $this->assertTrue(PDOMock::$rolledBack);
        $this->assertTrue(PDOMock::$transactionBegan);
    }

    /**
     * @test
     */
    public function WithOnConflictParamAndNonConflictPDOExceptionDuringTransaction_RollsBackAndThrowsOriginalException(): void
    {
        $this->expectException(\PDOException::class);

        PDOMock::$exception = new \PDOException('', 0);
        $this->proxy->transactionWithConflictException();
        $this->assertFalse(PDOMock::$committed);
        $this->assertTrue(PDOMock::$rolledBack);
        $this->assertTrue(PDOMock::$transactionBegan);
    }

    /**
     * @test
     */
    public function WithConflictPDOExceptionDuringTransaction_RollsBackAndThrowsException(): void
    {
        $this->expectException(TransactionConflictException::class);

        PDOMock::$exception = new \PDOException('', PDOTransactionAdapter::CONFLICT_SQL_STATE_CODE);
        $this->proxy->onlyTransaction();
        $this->assertFalse(PDOMock::$committed);
        $this->assertTrue(PDOMock::$rolledBack);
        $this->assertTrue(PDOMock::$transactionBegan);
    }

    /**
     * @test
     */
    public function WithOnConflictParamAndConflictPDOExceptionDuringTransaction_RollsBackAndThrowsConfiguredException(): void
    {
        $this->expectException(FunctionalConflictException::class);

        PDOMock::$exception = new \PDOException('', PDOTransactionAdapter::CONFLICT_SQL_STATE_CODE);
        $this->proxy->transactionWithConflictException();
        $this->assertFalse(PDOMock::$committed);
        $this->assertTrue(PDOMock::$rolledBack);
        $this->assertTrue(PDOMock::$transactionBegan);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $serviceProxyBuilder = $this->getServiceProxyBuilder(self::$cacheDir);
        $transactionAdapter = new PDOTransactionAdapter(new PDOMock());

        $this->proxy = $serviceProxyBuilder
            ->create(new TransactionAnnotationClass())
            ->withTransaction($transactionAdapter)
            ->build();
    }
}
