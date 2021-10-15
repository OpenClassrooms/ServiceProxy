<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator;

use Doctrine\Common\Annotations\AnnotationReader;
use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Annotations\ServiceProxyAnnotation;
use OpenClassrooms\ServiceProxy\Annotations\Transaction;
use OpenClassrooms\ServiceProxy\Exceptions\InvalidServiceProxyAnnotationException;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestBuilderInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\ServiceProxyCacheStrategy;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\ServiceProxyTransactionStrategy;
use OpenClassrooms\ServiceProxy\ServiceProxyTransactionInterface;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;
use OpenClassrooms\ServiceProxy\ServiceProxyInterface;
use OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface;

class ServiceProxyGenerator implements ProxyGeneratorInterface
{
    private AnnotationReader $annotationReader;

    private ServiceProxyCacheStrategy $cacheStrategy;

    private ServiceProxyTransactionStrategy $transactionStrategy;

    private ServiceProxyStrategyRequestBuilderInterface $serviceProxyStrategyRequestBuilder;

    /**
     * @throws \OpenClassrooms\ServiceProxy\Annotations\InvalidCacheIdException
     * @throws \OpenClassrooms\ServiceProxy\Exceptions\InvalidServiceProxyAnnotationException
     * @throws \ReflectionException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator): void
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);
        $classGenerator->setExtendedClass($originalClass->getName());
        $additionalInterfaces = [ServiceProxyInterface::class];
        $additionalProperties = [
            'proxy_realSubject' => new PropertyGenerator(
                'proxy_realSubject',
                null,
                AbstractMemberGenerator::FLAG_PRIVATE
            )
        ];
        $additionalMethods['setProxy_realSubject'] = new MethodGenerator(
            'setProxy_realSubject',
            [['name' => 'realSubject']],
            AbstractMemberGenerator::FLAG_PUBLIC,
            '$this->proxy_realSubject = $realSubject;'
        );

        $methods = $originalClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $preSource = '';
            $postSource = '';
            $exceptionSource = '';
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);
            foreach ($methodAnnotations as $methodAnnotation) {
                if ($methodAnnotation instanceof ServiceProxyAnnotation) {
                    $serviceProxyRequest = $this->serviceProxyStrategyRequestBuilder
                        ->create()
                        ->withAnnotation($methodAnnotation)
                        ->withClass($originalClass)
                        ->withMethod($method)
                        ->build();

                    if ($methodAnnotation instanceof Cache) {
                        $this->addCacheAnnotation($classGenerator);
                        $additionalInterfaces['cache'] = ServiceProxyCacheInterface::class;
                        $response = $this->cacheStrategy->execute($serviceProxyRequest);
                    } elseif ($methodAnnotation instanceof Transaction) {
                        $additionalInterfaces['transaction'] = ServiceProxyTransactionInterface::class;
                        $response = $this->transactionStrategy->execute($serviceProxyRequest);
                    } else {
                        throw new InvalidServiceProxyAnnotationException();
                    }

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
     * @throws \ReflectionException
     */
    private function generateProxyMethod(\ReflectionMethod $method, $preSource, $postSource, $exceptionSource): MethodGenerator
    {
        $methodReflection = new MethodReflection($method->getDeclaringClass()->getName(), $method->getName());
        if ('__construct' === $methodReflection->getName()) {
            $methodGenerator = MethodGenerator::fromArray(
                ['name' => $methodReflection->getName(), 'body' => '']
            );
        } else {
            $return = 'return ';
            if (
                ($returnType = $method->getReturnType()) instanceof \ReflectionNamedType
                && 'void' === $returnType->getName()
            ) {
                $return = '';
            }

            $methodGenerator = MethodGenerator::fromReflection($methodReflection);
            $parametersString = '(';
            $i = count($method->getParameters());
            foreach ($method->getParameters() as $parameter) {
                $parametersString .= '$'.$parameter->getName().(--$i > 0 ? ',' : '');
            }
            $parametersString .= ')';
            if ('' === $preSource && '' === $postSource && '' === $exceptionSource) {
                $body = $return . '$this->proxy_realSubject->'.$method->getName().$parametersString.";\n";
            } else {
                $body = "try {\n"
                    .$preSource."\n"
                    .'$data = $this->proxy_realSubject->'.$method->getName().$parametersString.";\n"
                    .$postSource."\n"
                    ."$return \$data;\n"
                    ."} catch(\\Exception \$e){\n"
                    .$exceptionSource."\n"
                    ."throw \$e;\n"
                    .'};';
            }
            $methodGenerator->setBody($body);
        }

        return $methodGenerator;
    }

    public function setAnnotationReader(AnnotationReader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    public function setCacheStrategy(ServiceProxyCacheStrategy $cacheStrategy): void
    {
        $this->cacheStrategy = $cacheStrategy;
    }

    public function setTransactionStrategy(ServiceProxyTransactionStrategy $transactionStrategy): void
    {
        $this->transactionStrategy = $transactionStrategy;
    }

    public function setServiceProxyStrategyRequestBuilder(
        ServiceProxyStrategyRequestBuilderInterface $serviceProxyStrategyRequestBuilder
    ): void {
        $this->serviceProxyStrategyRequestBuilder = $serviceProxyStrategyRequestBuilder;
    }

    private function addCacheAnnotation(ClassGenerator $classGenerator): void
    {
        $uses = $classGenerator->getUses();
        if (!in_array(Cache::class, $uses, true)){
            $classGenerator->addUse(Cache::class);
        }
    }
}
