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
use Zend\Code\Reflection\MethodReflection;

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
     * {@inheritdoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);
        $classGenerator->setExtendedClass($originalClass->getName());
        $additionalInterfaces = ['OpenClassrooms\\ServiceProxy\\ServiceProxyInterface'];
        $additionalProperties['proxy_realSubject'] = new PropertyGenerator(
            'proxy_realSubject',
            null,
            PropertyGenerator::FLAG_PRIVATE
        );
        $additionalMethods['setProxy_realSubject'] = new MethodGenerator(
            'setProxy_realSubject',
            [['name' => 'realSubject']],
            MethodGenerator::FLAG_PUBLIC,
            '$this->proxy_realSubject = $realSubject;'
        );

        $methods = $originalClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $preSource = '';
            $postSource = '';
            $exceptionSource = '';
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);
            foreach ($methodAnnotations as $methodAnnotation) {
                if ($methodAnnotation instanceof Cache) {
                    $additionalInterfaces['cache'] = 'OpenClassrooms\\ServiceProxy\\ServiceProxyCacheInterface';
                    $response = $this->cacheStrategy->execute(
                        $this->serviceProxyStrategyRequestBuilder
                            ->create()
                            ->withAnnotation($methodAnnotation)
                            ->withClass($originalClass)
                            ->withMethod($method)
                            ->build()
                    );
                    foreach ($response->getMethods() as $methodToAdd) {
                        $additionalMethods[$methodToAdd->getName()] = $methodToAdd;
                    }
                    foreach ($response->getProperties() as $propertyToAdd) {
                        $additionalProperties[$propertyToAdd->getName()] = $propertyToAdd;
                    }
                    $preSource .= $response->getPreSource();
                    $postSource .= $response->getPostSource();
                    $exceptionSource .= $response->getExceptionSource();
                }
            }
            $classGenerator->addMethodFromGenerator(
                $this->generateProxyMethod($method, $preSource, $postSource, $exceptionSource)
            );
        }

        $classGenerator->setImplementedInterfaces($additionalInterfaces);
        $classGenerator->addProperties($additionalProperties);
        $classGenerator->addMethods($additionalMethods);
    }

    /**
     * @return MethodGenerator
     */
    private function generateProxyMethod(\ReflectionMethod $method, $preSource, $postSource, $exceptionSource)
    {
        $methodReflection = new MethodReflection($method->getDeclaringClass()->getName(), $method->getName());
        $methodGenerator = MethodGenerator::fromReflection($methodReflection);

        $parametersString = '(';
        $i = count($method->getParameters());
        foreach ($method->getParameters() as $parameter) {
            $parametersString .= '$'.$parameter->getName().(--$i > 0 ? ',' : '');
        }
        $parametersString .= ')';
        if ('' === $preSource && '' === $postSource && '' === $exceptionSource) {
            $body = 'return $this->proxy_realSubject->'.$method->getName().$parametersString.";\n";
        } else {
            $body = "try {\n"
                .$preSource."\n"
                .'$data = $this->proxy_realSubject->'.$method->getName().$parametersString.";\n"
                .$postSource."\n"
                ."return \$data;\n"
                ."} catch(\\Exception \$e){\n"
                .$exceptionSource."\n"
                ."throw \$e;\n"
                .'};';
        }
        $methodGenerator->setBody($body);

        return $methodGenerator;
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
