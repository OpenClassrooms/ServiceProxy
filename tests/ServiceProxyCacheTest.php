<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Annotations\InvalidCacheIdException;
use OpenClassrooms\ServiceProxy\Helpers\ServiceProxyHelper;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheProviderDecoratorMock;
use OpenClassrooms\ServiceProxy\Tests\Doubles\ExceptionCacheAnnotationClass;
use PHPUnit\Framework\TestCase;

class ServiceProxyCacheTest extends TestCase
{
    use ServiceProxyHelper;

    use ServiceProxyTest;

    private CacheProviderDecorator $cacheProviderDecorator;

    private CacheAnnotationClass $proxy;

    /**
     * @test
     */
    public function CacheOnException_DonTSave(): void
    {
        try {
            $this->proxy->cacheMethodWithException();
            $this->fail('Exception should be thrown');
        } catch (\Exception $e) {
            $this->assertFalse(
                $this->cacheProviderDecorator->contains(
                    'OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::cacheMethodWithException'
                )
            );
        }
    }

    /**
     * @test
     */
    public function NotInCache_ReturnData(): void
    {
        $data = $this->proxy->onlyCache();
        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotationClass::DATA,
            $this->cacheProviderDecorator->fetch(
                md5('OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::onlyCache')
            )
        );
    }

    /**
     * @test
     */
    public function InCache_ReturnData(): void
    {
        $inCacheData = 'InCacheData';
        $this->cacheProviderDecorator->save(
            md5('OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::onlyCache'),
            $inCacheData
        );
        $data = $this->proxy->onlyCache();
        $this->assertEquals($inCacheData, $data);
    }

    /**
     * @test
     */
    public function WithLifeTime_ReturnData(): void
    {
        $data = $this->proxy->cacheWithLifeTime();
        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(60, CacheProviderDecoratorMock::$lifeTime);
    }

    /**
     * @test
     */
    public function TooLongId_WithId_ThrowException(): void
    {
        $this->expectException(InvalidCacheIdException::class);

        /** @var ExceptionCacheAnnotationClass $proxy */
        $proxy = $this->getServiceProxyBuilder(self::$cacheDir)
            ->create(new ExceptionCacheAnnotationClass())
            ->withCache($this->cacheProviderDecorator)
            ->build();

        $proxy->cacheWithTooLongId();
    }

    /**
     * @test
     */
    public function WithId_ReturnData(): void
    {
        $data = $this->proxy->cacheWithId();
        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(CacheAnnotationClass::DATA, $this->cacheProviderDecorator->fetch('test'));
    }

    /**
     * @test
     */
    public function WithIdAndParameters_ReturnData(): void
    {
        $data = $this->proxy->cacheWithIdAndParameters(new ParameterClassStub(), 'param 2');
        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(CacheAnnotationClass::DATA, $this->cacheProviderDecorator->fetch('test1'));
    }

    /**
     * @test
     */
    public function WithNamespace_ReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespace();

        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotationClass::DATA,
            $this->cacheProviderDecorator->fetch(
                $this->cacheProviderDecorator->fetch(md5('test-namespace')) .
                md5('OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::cacheWithNamespace')
            )
        );
    }

    /**
     * @test
     */
    public function WithNamespaceAndParameters_ReturnData(): void
    {
        $data = $this->proxy->cacheWithNamespaceAndParameters(new ParameterClassStub(), 'param 2');

        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotationClass::DATA,
            $this->cacheProviderDecorator->fetch(
                $this->cacheProviderDecorator->fetch(md5('test-namespace1')) .
                md5(
                    'OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::cacheWithNamespaceAndParameters'
                    . '::' . serialize(new ParameterClassStub()) . '::' . serialize('param 2')
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $serviceProxyBuilder = $this->getServiceProxyBuilder(self::$cacheDir);
        $this->cacheProviderDecorator = new CacheProviderDecoratorMock();
        $this->proxy = $serviceProxyBuilder
            ->create(new CacheAnnotationClass())
            ->withCache($this->cacheProviderDecorator)
            ->build();
    }
}
