<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAndTransactionAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CallsLog;
use OpenClassrooms\ServiceProxy\Tests\Doubles\LoggingCacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Tests\Doubles\LoggingTransactionAdapter;
use PHPUnit\Framework\TestCase;

class ServiceProxyAnnotationTest extends TestCase
{
    use ServiceProxyHelper;

    use ServiceProxyTest;

    private CacheAndTransactionAnnotationClass $proxy;

    /**
     * @test
     */
    public function WithCacheFirstAndCachedData_OnlyCallsFetch(): void
    {
        LoggingCacheProviderDecorator::$fetchReturn = 'cached';

        $result = $this->proxy->cacheThenTransactionMethodWithStringReturn();

        $this->assertEquals('cached', $result);

        $this->assertEquals(
            [
                [LoggingCacheProviderDecorator::class, 'fetchWithNamespace']
            ],
            CallsLog::getLogs()
        );
    }

    /**
     * @test
     */
    public function WithCacheFirstAndNoDataInCache_CallsMethodsInTheRightOrder(): void
    {
        LoggingCacheProviderDecorator::$fetchReturn = false;

        $result = $this->proxy->cacheThenTransactionMethodWithStringReturn();

        $this->assertEquals('stuff', $result);
        $this->assertEquals(
            [
                [LoggingCacheProviderDecorator::class, 'fetchWithNamespace'],
                [LoggingTransactionAdapter::class, 'isTransactionActive'],
                [LoggingTransactionAdapter::class, 'beginTransaction'],
                [LoggingCacheProviderDecorator::class, 'saveWithNamespace'],
                [LoggingTransactionAdapter::class, 'commit']
            ],
            CallsLog::getLogs()
        );
    }

    /**
     * @test
     */
    public function WithCacheFirstAndCachedDataAndException_OnlyCallsFetchAndReturnsCachedData(): void
    {
        LoggingCacheProviderDecorator::$fetchReturn = 'cached';

        $result = $this->proxy->cacheThenTransactionMethodWithException();

        $this->assertEquals('cached', $result);
        $this->assertEquals(
            [
                [LoggingCacheProviderDecorator::class, 'fetchWithNamespace']
            ],
            CallsLog::getLogs()
        );
    }

    /**
     * @test
     */
    public function WithCacheFirstAndNoDataInCacheAndException_CallsMethodsInTheRightOrderAndThrowsException(): void
    {
        LoggingCacheProviderDecorator::$fetchReturn = false;
        $this->expectException(\Exception::class);

        $this->proxy->cacheThenTransactionMethodWithException();

        $this->assertEquals(
            [
                [LoggingCacheProviderDecorator::class, 'fetchWithNamespace'],
                [LoggingTransactionAdapter::class, 'isTransactionActive'],
                [LoggingTransactionAdapter::class, 'beginTransaction'],
                [LoggingTransactionAdapter::class, 'rollback']
            ],
            CallsLog::getLogs()
        );
    }

    /**
     * @test
     */
    public function WithTransactionFirstAndCachedData_CallsMethodsInTheRightOrder(): void
    {
        LoggingCacheProviderDecorator::$fetchReturn = 'cached';

        $result = $this->proxy->transactionThenCacheMethodWithStringReturn();

        $this->assertEquals('cached', $result);
        $this->assertEquals(
            [
                [LoggingTransactionAdapter::class, 'isTransactionActive'],
                [LoggingTransactionAdapter::class, 'beginTransaction'],
                [LoggingCacheProviderDecorator::class, 'fetchWithNamespace']
            ],
            CallsLog::getLogs()
        );
    }

    /**
     * @test
     */
    public function WithTransactionFirstAndNoDataInCache_CallsMethodsInTheRightOrder(): void
    {
        LoggingCacheProviderDecorator::$fetchReturn = false;

        $result = $this->proxy->transactionThenCacheMethodWithStringReturn();

        $this->assertEquals('stuff', $result);
        $this->assertEquals(
            [
                [LoggingTransactionAdapter::class, 'isTransactionActive'],
                [LoggingTransactionAdapter::class, 'beginTransaction'],
                [LoggingCacheProviderDecorator::class, 'fetchWithNamespace'],
                [LoggingTransactionAdapter::class, 'commit'],
                [LoggingCacheProviderDecorator::class, 'saveWithNamespace']
            ],
            CallsLog::getLogs()
        );
    }

    /**
     * @test
     */
    public function WithTransactionFirstAndCachedDataAndException_CallsMethodsInTheRightOrder(): void
    {
        LoggingCacheProviderDecorator::$fetchReturn = 'cached';

        $result = $this->proxy->transactionThenCacheMethodWithException();

        $this->assertEquals('cached', $result);
        $this->assertEquals(
            [
                [LoggingTransactionAdapter::class, 'isTransactionActive'],
                [LoggingTransactionAdapter::class, 'beginTransaction'],
                [LoggingCacheProviderDecorator::class, 'fetchWithNamespace']
            ],
            CallsLog::getLogs()
        );
    }

    /**
     * @test
     */
    public function WithTransactionFirstAndNoDataInCacheAndException_CallsMethodsInTheRightOrderAndThrowsException(): void
    {
        LoggingCacheProviderDecorator::$fetchReturn = false;
        $this->expectException(\Exception::class);

        $this->proxy->transactionThenCacheMethodWithException();

        $this->assertEquals(
            [
                [LoggingTransactionAdapter::class, 'isTransactionActive'],
                [LoggingTransactionAdapter::class, 'beginTransaction'],
                [LoggingCacheProviderDecorator::class, 'fetchWithNamespace'],
                [LoggingTransactionAdapter::class, 'rollback']
            ],
            CallsLog::getLogs()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        CallsLog::reset();
        $serviceProxyBuilder = $this->getServiceProxyBuilder(self::$cacheDir);

        $this->proxy = $serviceProxyBuilder
            ->create(new CacheAndTransactionAnnotationClass())
            ->withTransaction(new LoggingTransactionAdapter())
            ->withCache(new LoggingCacheProviderDecorator())
            ->build();
    }
}
