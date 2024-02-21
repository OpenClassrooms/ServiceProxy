<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator\Method;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;

use OpenClassrooms\ServiceProxy\Generator\AccessInterceptorGenerator\Method\InterceptorGenerator;
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
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ): self {
        $method          = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $forwardedParams = [];

        foreach ($originalMethod->getParameters() as $parameter) {
            $forwardedParams[] = ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
        }

        $method->setBody(InterceptorGenerator::createInterceptedMethodBody(
            '$returnValue = parent::'
            . $originalMethod->getName() . '(' . implode(', ', $forwardedParams) . ');',
            $method,
            $prefixInterceptors,
            $suffixInterceptors,
            $originalMethod
        ));

        return $method;
    }
}
