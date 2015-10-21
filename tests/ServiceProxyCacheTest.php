<?php

namespace OpenClassrooms\ServiceProxy\Tests;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass;
use OpenClassrooms\ServiceProxy\Tests\Doubles\CacheProviderDecoratorMock;

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
        $data = $this->proxy->onlyCacheMethod();
        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotationClass::DATA,
            $this->cacheProviderDecorator->fetch(
                md5('OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::onlyCacheMethod')
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
            md5('OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::onlyCacheMethod'),
            $inCacheData
        );
        $data = $this->proxy->onlyCacheMethod();
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
     */
    public function WithNamespace_ReturnData()
    {
        $data = $this->proxy->cacheWithNamespaceMethod();

        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotationClass::DATA,
            $this->cacheProviderDecorator->fetch(
                $this->cacheProviderDecorator->fetch(md5('test-namespace')).
                md5('OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::cacheWithNamespaceMethod')
            )
        );
    }

    /**
     * @test
     */
    public function WithNamespaceAndParameters_ReturnData()
    {
        $data = $this->proxy->cacheWithNamespaceAndParametersMethod(new ParameterClassStub(), 'param 2');

        $this->assertEquals(CacheAnnotationClass::DATA, $data);
        $this->assertEquals(
            CacheAnnotationClass::DATA,
            $this->cacheProviderDecorator->fetch(
                $this->cacheProviderDecorator->fetch(md5('test-namespace1')).
                md5(
                    'OpenClassrooms\ServiceProxy\Tests\Doubles\CacheAnnotationClass::cacheWithNamespaceAndParametersMethod'
                    .'::'.serialize(new ParameterClassStub()).'::'.serialize('param 2')
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->cacheProviderDecorator = new CacheProviderDecoratorMock();
        $this->proxy = $this->buildServiceProxyBuilder()
            ->create(new CacheAnnotationClass())
            ->withCache($this->cacheProviderDecorator)
            ->build();
    }
//
//    /**
//     * @test
//     */
//    public function CachedWithNamespace_Cache_ReturnResponse()
//    {
//        $this->useCaseProxy->setUseCase(new NamespaceCacheUseCaseStub());
//        $this->useCaseProxy->execute(new UseCaseRequestStub());
//        $this->assertTrue($this->cache->savedWithNamespace);
//        $this->cache->savedWithNamespace = false;
//        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
//        $this->assertEquals(new UseCaseResponseStub(), $response);
//        $this->assertTrue($this->cache->fetched);
//        $this->assertFalse($this->cache->savedWithNamespace);
//    }
//
//    /**
//     * @test
//     */
//    public function WithLifeTime_Cache_ReturnResponse()
//    {
//        $this->useCaseProxy->setUseCase(new LifeTimeCacheUseCaseStub());
//        $response = $this->useCaseProxy->execute(new UseCaseRequestStub());
//        $this->assertEquals(new UseCaseResponseStub(), $response);
//        $this->assertTrue($this->cache->saved);
//        $this->assertEquals(LifeTimeCacheUseCaseStub::LIFETIME, $this->cache->lifeTime);
//    }
//
//    /**
//     * @test
//     */
//    public function CacheOnException_DonTSave()
//    {
//        try {
//            $this->useCaseProxy->setUseCase(new ExceptionCacheUseCaseStub());
//            $this->useCaseProxy->execute(new UseCaseRequestStub());
//            $this->fail('Exception should be thrown');
//        } catch (UseCaseException $e) {
//            $this->assertFalse($this->cache->saved);
//        }
//    }
}
