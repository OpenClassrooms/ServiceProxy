<?php

namespace OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestBuilderInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\ServiceProxyCacheStrategy;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * @author Romain Kuzniak <romain.kuzniak@openclassrooms.com>
 */
class ServiceProxyGenerator implements ProxyGeneratorInterface
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var ServiceProxyCacheStrategy
     */
    private $cacheStrategy;

    /**
     * @var ServiceProxyStrategyRequestBuilderInterface
     */
    private $serviceProxyStrategyRequestBuilder;

    /**
     * @inheritDoc
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces = ['OpenClassrooms\\ServiceProxy\\ServiceProxyInterface'];
        $classGenerator->setExtendedClass($originalClass->getName());

        list($methodsAnnotations, $annotationTypes) = $this->getMethodsAnnotations($originalClass);
        if (isset($annotationTypes['Cache'])) {
            $this->buildClassCacheStrategy($classGenerator);
            $interfaces[] = 'OpenClassrooms\\ServiceProxy\\ServiceProxyCacheInterface';
        }
        foreach ($methodsAnnotations as $method => $annotations) {
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Cache) {
                    $response = $this->cacheStrategy->execute(
                        $this->serviceProxyStrategyRequestBuilder
                            ->create()
                            ->withAnnotation($annotation)
                            ->build()
                    );
                    $classGenerator->addMethods($response->getMethods());
                }
            }
        }

        $classGenerator->setImplementedInterfaces($interfaces);
    }

    /**
     * @return array
     */
    private function getMethodsAnnotations(ReflectionClass $originalClass)
    {
        $methods = $originalClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methodsAnnotations = [];
        $annotationTypes = [];
        foreach ($methods as $method) {
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);
            foreach ($methodAnnotations as $annotation) {
                if (!isset($annotationTypes['Cache']) && $annotation instanceof Cache) {
                    $annotationTypes['Cache'] = true;
                }
            }
            $methodsAnnotations[$method->getName()] = $methodAnnotations;
        }

        return array($methodsAnnotations, $annotationTypes);
    }

    private function buildClassCacheStrategy(ClassGenerator $classGenerator)
    {
        $classGenerator->addProperty('cacheProvider', null, PropertyGenerator::FLAG_PRIVATE);
        $classGenerator->addMethod(
            'setCacheProvider',
            [
                [
                    'name' => 'cacheProvider',
                    'type' => '\\OpenClassrooms\\DoctrineCacheExtension\\CacheProviderDecorator'
                ]
            ],
            MethodGenerator::FLAG_PUBLIC,
            '$this->cacheProvider = $cacheProvider;'
        );
    }

    public function setAnnotationReader(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function setCacheStrategy(ServiceProxyCacheStrategy $cacheStrategy)
    {
        $this->cacheStrategy = $cacheStrategy;
    }

    public function setServiceProxyStrategyRequestBuilder(
        ServiceProxyStrategyRequestBuilderInterface $serviceProxyStrategyRequestBuilder
    )
    {
        $this->serviceProxyStrategyRequestBuilder = $serviceProxyStrategyRequestBuilder;
    }
}
