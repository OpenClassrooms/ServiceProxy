<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidCacheProviderException;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidServiceProxyAnnotationException;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidTransactionAdapterException;
use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;
use OpenClassrooms\ServiceProxy\ServiceProxyFactory;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAndTransactionAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationWithConstructorClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\PDOMock;
use OpenClassrooms\ServiceProxy\Tests\Doubles\TransactionAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\TransactionAnnotationWithConstructorClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\UnsupportedServiceProxyAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\WithoutAnnotationClass;
use OpenClassrooms\ServiceProxy\Transaction\PDOTransactionAdapter;
use PHPUnit\Framework\TestCase;

class ServiceProxyFactoryTest extends TestCase
{
    use ServiceProxyHelper;

    use ServiceProxyTest;

    private ServiceProxyFactory $factory;

    /**
     * @test
     */
    public function WithoutAnnotation_ReturnServiceProxyInterface(): void
    {
        $inputClass = new WithoutAnnotationClass();
        $inputClass->field = true;

        /** @var WithoutAnnotationClass|ServiceProxyInterface $proxy */
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertProxy($inputClass, $proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
        $this->assertTrue($proxy->aMethodWithoutServiceProxyAnnotation());

        $this->assertNotServiceCacheProxy($proxy);
        $this->assertNotServiceTransactionProxy($proxy);
    }

    /**
     * @test
     */
    public function WithCacheAnnotationWithoutCacheProvider_ThrowException(): void
    {
        $this->expectException(InvalidCacheProviderException::class);

        $inputClass = new CacheAnnotationClass();
        $this->factory->createProxy($inputClass);
    }

    /**
     * @test
     */
    public function WithCacheAnnotation_ReturnServiceProxyCacheInterface(): void
    {
        $inputClass = new CacheAnnotationClass();

        $this->factory->setCacheProvider(new CacheProviderDecorator(new ArrayCache()));

        /** @var ServiceProxyInterface|CacheAnnotationClass $proxy */
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertServiceCacheProxy($inputClass, $proxy);
        $this->assertNotServiceTransactionProxy($proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    /**
     * @test
     */
    public function WithCacheAnnotationWithConstructor_ReturnServiceProxyCacheInterface(): void
    {
        $inputClass = new CacheAnnotationWithConstructorClass('test');

        $this->factory->setCacheProvider(new CacheProviderDecorator(new ArrayCache()));

        /** @var ServiceProxyInterface|CacheAnnotationClass $proxy */
        $proxy = $this->factory->createProxy($inputClass);

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
        $this->factory->createProxy($inputClass);
    }

    /**
     * @test
     */
    public function WithTransactionAnnotation_ReturnServiceProxyTransactionInterface(): void
    {
        $inputClass = new TransactionAnnotationClass();

        $this->factory->setTransactionAdapter(new PDOTransactionAdapter(new PDOMock()));

        /** @var ServiceProxyInterface|TransactionAnnotationClass $proxy */
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertServiceTransactionProxy($inputClass, $proxy);
        $this->assertNotServiceCacheProxy($proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    /**
     * @test
     */
    public function WithTransactionAnnotationWithConstructor_ReturnServiceProxyTransactionInterface(): void
    {
        $inputClass = new TransactionAnnotationWithConstructorClass('test');

        $this->factory->setTransactionAdapter(new PDOTransactionAdapter(new PDOMock()));

        /** @var ServiceProxyInterface|TransactionAnnotationWithConstructorClass $proxy */
        $proxy = $this->factory->createProxy($inputClass);

        $this->assertServiceTransactionProxy($inputClass, $proxy);
        $this->assertNotServiceCacheProxy($proxy);
        $this->assertTrue($proxy->aMethodWithoutAnnotation());
    }

    /**
     * @test
     */
    public function WithCacheAndTransactionAnnotationWithConstructor_ReturnServiceProxyWithInterfaces(): void
    {
        $inputClass = new CacheAndTransactionAnnotationClass();

        $this->factory->setTransactionAdapter(new PDOTransactionAdapter(new PDOMock()));
        $this->factory->setCacheProvider(new CacheProviderDecorator(new ArrayCache()));

        /** @var ServiceProxyInterface|CacheAndTransactionAnnotationClass $proxy */
        $proxy = $this->factory->createProxy($inputClass);

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

        $this->factory->createProxy($inputClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = $this->getServiceProxyFactory(self::$cacheDir);
    }
}
