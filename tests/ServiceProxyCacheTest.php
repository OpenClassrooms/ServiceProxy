<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheProviderDecoratorMock;
use OpenClassrooms\ServiceProxy\Tests\Doubles\ExceptionCacheAnnotationClass;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyCacheTest extends \PHPUnit_Framework_TestCase
{
    use ServiceProxyTest;

    /**
     * @var CacheProviderDecorator
     */
    private $cacheProviderDecorator;

    /**
     * @var CacheAnnotationClass
     */
    private $proxy;

    /**
     * @test
     */
    public function CacheOnException_DonTSave()
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
    public function NotInCache_ReturnData()
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
    public function InCache_ReturnData()
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
    public function WithLifeTime_ReturnData()
    {
        $data = $this->proxy->cacheWithLifeTime();
        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(60, CacheProviderDecoratorMock::$lifeTime);
    }

    /**
     * @test
     * @expectedException \OpenClassrooms\ServiceProxy\Annotations\InvalidCacheIdException
     */
    public function TooLongId_WithId_ThrowException()
    {
        /** @var ExceptionCacheAnnotationClass $proxy */
        $proxy = $this->buildServiceProxyBuilder()
            ->create(new ExceptionCacheAnnotationClass())
            ->withCache($this->cacheProviderDecorator)
            ->build();
        $proxy->cacheWithTooLongId();
    }

    /**
     * @test
     */
    public function WithId_ReturnData()
    {
        $data = $this->proxy->cacheWithId();
        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(CacheAnnotationClass::DATA, $this->cacheProviderDecorator->fetch('test'));
    }

    /**
     * @test
     */
    public function WithIdAndParameters_ReturnData()
    {
        $data = $this->proxy->cacheWithIdAndParameters(new ParameterClassStub(), 'param 2');
        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(CacheAnnotationClass::DATA, $this->cacheProviderDecorator->fetch('test1'));
    }

    /**
     * @test
     */
    public function WithNamespace_ReturnData()
    {
        $data = $this->proxy->cacheWithNamespace();

        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotationClass::DATA,
            $this->cacheProviderDecorator->fetch(
                $this->cacheProviderDecorator->fetch(md5('test-namespace')).
                md5('OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::cacheWithNamespace')
            )
        );
    }

    /**
     * @test
     */
    public function WithNamespaceAndParameters_ReturnData()
    {
        $data = $this->proxy->cacheWithNamespaceAndParameters(new ParameterClassStub(), 'param 2');

        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotationClass::DATA,
            $this->cacheProviderDecorator->fetch(
                $this->cacheProviderDecorator->fetch(md5('test-namespace1')).
                md5(
                    'OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::cacheWithNamespaceAndParameters'
                    .'::'.serialize(new ParameterClassStub()).'::'.serialize('param 2')
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->cacheProviderDecorator = new CacheProviderDecoratorMock();
        $this->proxy = $this->buildServiceProxyBuilder()
            ->create(new CacheAnnotationClass())
            ->withCache($this->cacheProviderDecorator)
            ->build();
    }
}
