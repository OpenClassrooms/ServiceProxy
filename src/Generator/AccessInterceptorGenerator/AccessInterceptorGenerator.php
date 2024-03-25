<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator\Method\InterceptedMethod;
use OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator\Method\SetMethodPrefixInterceptors;
use OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator\Method\SetMethodSuffixInterceptors;
use OpenClassrooms\ServiceProxy\Proxy\AccessInterceptorsInterface;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

class AccessInterceptorGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @param array{'methods'?: array<string>} $proxyOptions
     *
     * @throws \InvalidArgumentException
     * @throws InvalidProxiedClassException
     */
    public function generate(\ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = [])
    {
        if (!\array_key_exists('methods', $proxyOptions)) {
            throw new \InvalidArgumentException(sprintf('Generator %s needs a methods proxyOptions', __CLASS__));
        }

        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([AccessInterceptorsInterface::class]);
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodSuffixInterceptors());

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildMethodInterceptor($prefixInterceptors, $suffixInterceptors),
                    array_map(
                        static fn (string $method) => $originalClass->getMethod($method),
                        $proxyOptions['methods']
                    )
                ),
                [
                    new SetMethodPrefixInterceptors($prefixInterceptors),
                    new SetMethodSuffixInterceptors($suffixInterceptors),
                ]
            )
        );
    }

    private function buildMethodInterceptor(
        MethodPrefixInterceptors $prefixInterceptors,
        MethodSuffixInterceptors $suffixInterceptors
    ): callable {
        return static function (\ReflectionMethod $method) use (
            $prefixInterceptors,
            $suffixInterceptors
        ): InterceptedMethod {
            return InterceptedMethod::generateMethod(
                new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                $prefixInterceptors,
                $suffixInterceptors
            );
        };
    }
}
