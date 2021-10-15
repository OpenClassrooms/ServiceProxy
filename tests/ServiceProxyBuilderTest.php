<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidServiceProxyAnnotationException;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidTransactionAdapterException;
use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;
use OpenClassrooms\ServiceProxy\ServiceProxyBuilderInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAndTransactionAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\PDOMock;
use OpenClassrooms\ServiceProxy\Tests\Doubles\TransactionAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\UnsupportedServiceProxyAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\WithoutAnnotationClass;
use OpenClassrooms\ServiceProxy\Transaction\PDOTransactionAdapter;
use PHPUnit\Framework\TestCase;

class ServiceProxyBuilderTest extends TestCase
{
    use ServiceProxyHelper;

    use ServiceProxyTest;

    private ServiceProxyBuilderInterface $builder;

    /**
     * @test
     */
    public function WithoutAnnotation_ReturnServiceProxyInterface(): void
    {
        $inputClass = new WithoutAnnotationClass();
        $inputClass->field = true;
        /** @var WithoutAnnotationClass|ServiceProxyInterface $proxy */
        $proxy = $this->builder
            ->create($inputClass)
            ->build();

        $this->assertProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
        $this->assertTrue($proxy->aMethodWithoutServiceProxyAnnotation());

        $this->assertNotServiceCacheProxy($proxy);
        $this->assertNotServiceTransactionProxy($proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    /**
     * @test
     */
    public function WithCacheAnnotationWithoutCacheProvider_ThrowException(): void
    {
        $this->expectException(InvalidCacheProviderException::class);

        $inputClass = new CacheAnnotationClass();

        /* @var \OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface|CacheAnnotationClass $proxy */
        $this->builder->create($inputClass)->build();
    }

    /**
     * @test
     */
    public function WithCacheAnnotation_ReturnServiceProxyCacheInterface(): void
    {
        $inputClass = new CacheAnnotationClass();

        /** @var \OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface|CacheAnnotationClass $proxy */
        $proxy = $this->builder
            ->create($inputClass)
            ->withCache(new CacheProviderDecorator(new ArrayCache()))
            ->build();

        $this->assertServiceCacheProxy($inputClass, $proxy);
        $this->assertNotServiceTransactionProxy($proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    /**
     * @test
     */
    public function WithTransactionAnnotationWithoutTransactionAdapter_ThrowException(): void
    {
        $this->expectException(InvalidTransactionAdapterException::class);

        $inputClass = new TransactionAnnotationClass();

        /* @var \OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface|TransactionAnnotationClass $proxy */
        $this->builder->create($inputClass)->build();
    }

    /**
     * @test
     */
    public function WithTransactionAnnotation_ReturnServiceProxyTransactionInterface(): void
    {
        $inputClass = new TransactionAnnotationClass();

        /** @var \OpenClassrooms\ServiceProxy\ServiceProxyTransactionInterface|TransactionAnnotationClass $proxy */
        $proxy = $this->builder
            ->create($inputClass)
            ->withTransaction(new PDOTransactionAdapter(new PDOMock()))
            ->build();

        $this->assertServiceTransactionProxy($inputClass, $proxy);
        $this->assertNotServiceCacheProxy($proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    /**
     * @test
     */
    public function WithCacheAndTransactionAnnotation_ReturnServiceProxyCacheAndTransactionInterface(): void
    {
        $inputClass = new CacheAndTransactionAnnotationClass();

        /**
         * @var \OpenClassrooms\ServiceProxy\ServiceProxyTransactionInterface|\OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface|CacheAndTransactionAnnotationClass $proxy
         */
        $proxy = $this->builder
            ->create($inputClass)
            ->withCache(new CacheProviderDecorator(new ArrayCache()))
            ->withTransaction(new PDOTransactionAdapter(new PDOMock()))
            ->build();

        $this->assertServiceCacheProxy($inputClass, $proxy);
        $this->assertServiceTransactionProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    /**
     * @test
     */
    public function WithUnsupportedAnnotation_ThrowException(): void
    {
        $this->expectException(InvalidServiceProxyAnnotationException::class);

        $inputClass = new UnsupportedServiceProxyAnnotationClass();

        $this->builder->create($inputClass)->build();
    }

    protected function setUp(): void
    {
        $this->builder = $this->getServiceProxyBuilder(self::$cacheDir);
    }
}
