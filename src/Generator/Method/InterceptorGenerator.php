<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Generator\Method;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;

use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;

/**
 * Utility to create pre- and post- method interceptors around a given method body
 *
 * @private - this class is just here as a small utility for this component, don't use it in your own code
 */
final class InterceptorGenerator
{
    private const TEMPLATE = <<<'PHP'
if (isset($this->{{$prefixInterceptorsName}}[{{$name}}])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->{{$prefixInterceptorsName}}[{{$name}}]->__invoke($this, $this->{{$valueHolderName}}, {{$name}}, {{$paramsString}}, $returnEarly);

    if ($returnEarly) {
        {{$returnEarlyPrefixExpression}}
    }
}

$exception = null;
try {
   {{$methodBody}}
} catch (\Exception $e) {
    $exception = $e;
}

if (isset($this->{{$suffixInterceptorsName}}[{{$name}}])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->{{$suffixInterceptorsName}}[{{$name}}]->__invoke($this, $this->{{$valueHolderName}}, {{$name}}, {{$paramsString}}, $exception ?? $returnValue, $returnEarly);

    if ($returnEarly) {
        {{$returnEarlySuffixExpression}}
    }
}

if ($exception) {
    throw $exception;
}

{{$returnExpression}}
PHP;

    /**
     * @param string $methodBody the body of the previously generated code.
     *                           It MUST assign the return value to a variable
     *                           `$returnValue` instead of directly returning
     */
    public static function createInterceptedMethodBody(
        string $methodBody,
        MethodGenerator $method,
        PropertyGenerator $valueHolder,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors,
        ?\ReflectionMethod $originalMethod
    ): string {
        $name = var_export($method->getName(), true);
        $valueHolderName = $valueHolder->getName();
        $prefixInterceptorsName = $prefixInterceptors->getName();
        $suffixInterceptorsName = $suffixInterceptors->getName();
        $params = [];

        foreach ($method->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $params[] = var_export($parameterName, true) . ' => $' . $parameter->getName();
        }

        $paramsString = 'array(' . implode(', ', $params) . ')';

        $replacements = [
            '{{$prefixInterceptorsName}}' => $prefixInterceptorsName,
            '{{$name}}' => $name,
            '{{$valueHolderName}}' => $valueHolderName,
            '{{$paramsString}}' => $paramsString,
            '{{$returnEarlyPrefixExpression}}' => ProxiedMethodReturnExpression::generate(
                '$prefixReturnValue',
                $originalMethod
            ),
            '{{$methodBody}}' => $methodBody,
            '{{$suffixInterceptorsName}}' => $suffixInterceptorsName,
            '{{$returnEarlySuffixExpression}}' => ProxiedMethodReturnExpression::generate(
                '$suffixReturnValue',
                $originalMethod
            ),
            '{{$returnExpression}}' => ProxiedMethodReturnExpression::generate('$returnValue', $originalMethod),

        ];

        return str_replace(array_keys($replacements), $replacements, self::TEMPLATE);
    }
}
