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
        $classGenerator->setExtendedClass($originalClass->getName());

        $methodsAnnotations = $this->getMethodsAnnotations($originalClass);

        $interfacesToAdd = ['OpenClassrooms\\ServiceProxy\\ServiceProxyInterface'];
        $propertiesToAdd = [];
        $methodsToAdd = [];
        foreach ($methodsAnnotations as $methodAnnotation) {
            foreach ($methodAnnotation['annotations'] as $annotation) {
                if ($annotation instanceof Cache) {
                    $interfacesToAdd['cache'] = 'OpenClassrooms\\ServiceProxy\\ServiceProxyCacheInterface';
                    $response = $this->cacheStrategy->execute(
                        $this->serviceProxyStrategyRequestBuilder
                            ->create()
                            ->withAnnotation($annotation)
                            ->withClass($originalClass)
                            ->withMethod($methodAnnotation['method'])
                            ->build()
                    );
                    foreach ($response->getMethods() as $methodToAdd) {
                        $methodsToAdd[$methodToAdd->getName()] = $methodToAdd;
                    }
                    foreach ($response->getProperties() as $propertyToAdd) {
                        $propertiesToAdd[] = $propertyToAdd;
                    }
                }
            }
        }
        $classGenerator->setImplementedInterfaces($interfacesToAdd);
        $classGenerator->addProperties($propertiesToAdd);
        $classGenerator->addMethods($methodsToAdd);
    }

    /**
     * @return [][]
     */
    private function getMethodsAnnotations(ReflectionClass $originalClass)
    {
        $methodsAnnotations = [];

        $methods = $originalClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);
            if (!empty($methodAnnotations)) {
                $methodsAnnotations[$method->getName()] = ['method' => $method, 'annotations' => []];
                foreach ($methodAnnotations as $annotation) {
                    if ($annotation instanceof Cache) {
                        $methodsAnnotations[$method->getName()]['annotations'][] = $annotation;
                    }
                }
            }
        }

        return $methodsAnnotations;
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
    ) {
        $this->serviceProxyStrategyRequestBuilder = $serviceProxyStrategyRequestBuilder;
    }
}
