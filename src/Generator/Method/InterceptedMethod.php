<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Generator\Method;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PromotedParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionMethod;

use ProxyManager\Generator\MethodGenerator;

/**
 * Method with additional pre- and post- interceptor logic in the body
 */
final class InterceptedMethod extends MethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $valueHolderProperty,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ): self {
        $method = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $forwardedParams = [];

        foreach ($originalMethod->getParameters() as $parameter) {
            $forwardedParams[] = ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
        }

        $method->setBody(InterceptorGenerator::createInterceptedMethodBody(
            '$returnValue = $this->' . $valueHolderProperty->getName() . '->'
            . $originalMethod->getName() . '(' . implode(', ', $forwardedParams) . ');',
            $method,
            $valueHolderProperty,
            $prefixInterceptors,
            $suffixInterceptors,
            $originalMethod
        ));

        return $method;
    }

    /**
     * @return static
     */
    public static function fromReflectionWithoutBodyAndDocBlock(MethodReflection $reflectionMethod): self
    {
        /** @var static $method */
        $method = parent::fromReflectionWithoutBodyAndDocBlock($reflectionMethod);

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $method->setParameter(
                $reflectionParameter->isPromoted()
                    ? PromotedParameterGenerator::fromReflection($reflectionParameter)
                    : InterceptedParameter::fromReflection($reflectionParameter)
            );
        }

        return $method;
    }
}
