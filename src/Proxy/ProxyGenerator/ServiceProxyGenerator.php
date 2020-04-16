<?php

namespace OpenClassrooms\ServiceProxy\Proxy\ProxyGenerator;

use Doctrine\Common\Annotations\AnnotationReader;
use OpenClassrooms\ServiceProxy\Annotations\Cache;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\Request\ServiceProxyStrategyRequestBuilderInterface;
use OpenClassrooms\ServiceProxy\Proxy\Strategy\ServiceProxyCacheStrategy;
use OpenClassrooms\ServiceProxy\ServiceProxyCacheInterface;
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

        $propsInit = [];
        $properties = $originalClass->getProperties();
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $getter = 'get'.ucfirst($propertyName);
            $flag = PropertyGenerator::FLAG_PUBLIC;
            if ($property->isPrivate()) {
                $flag = PropertyGenerator::FLAG_PRIVATE;
            } 
            if ($property->isProtected()) {
                $flag = PropertyGenerator::FLAG_PROTECTED;
            }
            $classGenerator->addProperty(
                $propertyName,
                null,
                $flag
            );
            $propsInit[] = "\${$getter} = function() {return \$this->{$propertyName};};
            \$this->{$propertyName} = \${$getter}->call(\$realSubject);";
        }
        
        $additionalMethods['setProxy_realSubject'] = new MethodGenerator(
            'setProxy_realSubject',
            [['name' => 'realSubject']],
            MethodGenerator::FLAG_PUBLIC,
            "\$this->proxy_realSubject = \$realSubject;\n" . implode("\n", $propsInit)
        );

        $methods = $originalClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $preSource = '';
            $postSource = '';
            $exceptionSource = '';
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);
            foreach ($methodAnnotations as $methodAnnotation) {
                $classGenerator->addUse(get_class($methodAnnotation));
                if ($methodAnnotation instanceof Cache) {
                    $additionalInterfaces['cache'] = ServiceProxyCacheInterface::class;
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

            $methodReflection = new MethodReflection($method->getDeclaringClass()->getName(), $method->getName());

            $generated = $this->generateProxyMethod($methodReflection, $preSource, $postSource, $exceptionSource);
            if ($generated) {
                $classGenerator->addMethodFromGenerator($generated);
            }
        }
        $classGenerator->setImplementedInterfaces($additionalInterfaces);
        $classGenerator->addProperties($additionalProperties);
        $classGenerator->addMethods($additionalMethods);
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

    /**
     * @return MethodGenerator|null
     */
    private function generateProxyMethod(MethodReflection $method, $preSource, $postSource, $exceptionSource)
    {
        $methodGenerator = MethodGenerator::fromReflection($method);
        if ($method->getName() === '__construct') {
            return MethodGenerator::fromArray(
                [
                    'name' => '__construct',
                    'body' => null,
                ]
            );
        }
        if ('' === $preSource && '' === $postSource && '' === $exceptionSource) {
            return null;
        }
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $parameters[] .= '$' . $parameter->getName();
        }
        $parametersString = implode(', ', $parameters);
        $body = "
        try {
            $preSource
            \$data = \$this->proxy_realSubject->{$method->getName()}($parametersString);
            $postSource
            return \$data;
        } catch(\\Exception \$e) {
            $exceptionSource
            throw \$e;
        }";
        $methodGenerator->setBody($body);

        return $methodGenerator;
    }
}
