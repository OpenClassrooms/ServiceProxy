<?php

namespace OpenClassrooms\ServiceProxy\Generator\Method;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Reflection\ParameterReflection;

class InterceptedParameter extends ParameterGenerator
{
    /**
     * @return ParameterGenerator
     */
    public static function fromReflection(ParameterReflection $reflectionParameter)
    {
        $param = parent::fromReflection($reflectionParameter);
        $param->type = null;

        return $param;
    }
}
